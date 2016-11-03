<?php

namespace Tn\Bundle\PathNavigationBundle;

use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\DataProvider\DataProviderInterface;

/**
 * PathNavigationProvider
 *
 * @author Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 */
class PathNavigationProvider implements DataProviderInterface
{
    /**
     * @var \Sculpin\Core\DataProvider\DataProviderInterface
     */
    private $dataProvider;
    private $articlesDataProvider;

    /**
     * @var mixed
     */
    private $computedData = false;

    /**
     * Constructor
     *
     * @param \Sculpin\Core\DataProvider\DataProviderInterface $dataProviderManager
     */
    public function __construct(
        DataProviderInterface $dataProviderManager,
        DataProviderInterface $articlesDataProviderManager
        )
    {
        $this->dataProvider = $dataProviderManager;
        $this->articlesDataProvider = $articlesDataProviderManager;
    }
    
    private function processContentTypeData($providerData, &$data, &$all_paths, $content_type)
    {
      foreach ($providerData as $post) {
        //https://github.com/sculpin/sculpin/blob/master/src/Sculpin/Bundle/PaginationBundle/PaginationGenerator.php#L101
        $sourcePermalinkFactory = new SourcePermalinkFactory('');
        $permalink = $sourcePermalinkFactory->create($post);
        $post_relative_url = $permalink->relativeUrlPath();
        $post_relative_url = trim($post_relative_url, '/');
        if($this->endsWith($post_relative_url, ".md")){
          $post_relative_url_array = explode('/', $post_relative_url);
          $length = count($post_relative_url_array);
          // 2 to account preceeding path: open-curricula-files/_videos in sculpin_kernel.yml
          $post_relative_url_array = array_splice($post_relative_url_array, 2, $length-3);
          for($i = 1; $i <= $length; $i++) {
            $clone_post_relative_url_array = $post_relative_url_array;
            $spliced_arr = array_splice($clone_post_relative_url_array, 0, $i);
            $spliced_relative_url = implode('/', $spliced_arr);
            $arr[$spliced_relative_url]['posts'][$post_relative_url] = $post;
            $arr[$spliced_relative_url]['apis']['items'][$post_relative_url] = array(
                "title" => $post->title(),
                "url" => $post_relative_url,
                "content" => $post->content(),
                "tags" => $post->data()->get('tags'),
            );
            $thumbnail_urls = $post->data()->get('thumbnail_urls');
            if(!empty($thumbnail_urls)){
              $arr[$spliced_relative_url]['apis']['items'][$post_relative_url]["thumbnails"] = $thumbnail_urls;
            }
            if($content_type == "videos"){
              $arr[$spliced_relative_url]['apis']['items'][$post_relative_url]["youtube_id"] = $post->data()->get('youtube_id');
            }elseif($content_type == "articles"){
              $arr[$spliced_relative_url]['apis']['items'][$post_relative_url]["article_ref"] = $post->data()->get('article_ref');
            }
            $all_paths[$spliced_relative_url][] = $spliced_relative_url;
          }
          foreach ($arr as $key=>$val) {
            $r = & $data;
            foreach (explode("/", $key) as $key) {
              if (!isset($r[$key])) {
                $r[$key] = array();
              }
              $r = & $r[$key];
            }
            $r = $val;
          }
        }
      }
    }

    public function endsWith($haystack, $needle)
    {
      $length = strlen($needle);
      if ($length == 0) {
        return true;
      }

      return (substr($haystack, -$length) === $needle);
    }

    /**
     * Provide data about post organized per year and couple year/month
     *
     * Return array(
     *   'year' = > array of posts,
     *   'year-month' => array of posts
     * )
     *
     * @return array
     */
    public function provideData()
    {
        if ($this->computedData !== false) {
            return $this->computedData;
        }

        $videos_data = array();
        $articles_data = array();
        $data = array();
        $out = array();
        
        $all_paths = array();
        $hierarchical_paths = array();
        
        $this->processContentTypeData($this->dataProvider->provideData(), $videos_data, $all_paths, "videos");
        //ladybug_dump($data);
        $this->processContentTypeData($this->articlesDataProvider->provideData(), $articles_data, $all_paths, "articles");
        $data = array_merge_recursive($videos_data, $articles_data);
        
        
        
        //ladybug_dump($all_paths);
        foreach ($all_paths as $key=>$val) {
          $r = & $hierarchical_paths;
          foreach (explode("/", $key) as $key) {
            if (!isset($r[$key])) {
              $r[$key] = array();
            }
            $r = & $r[$key];
          }
          //$r = $val;
        }
        //ladybug_dump($hierarchical_paths);
        $api_tn_directories = $this->hierarchical_path_array_to_api_array($hierarchical_paths);
        //ladybug_dump($api_tn_directories);
        $data['tn_directories'] = $api_tn_directories;
        $this->computedData = $data;
        return $data;
    }

    public function hierarchical_path_array_to_api_array($hierarchical_paths, $path = '')
    {
      $output = array();
      foreach ($hierarchical_paths as $key=>$val) {
        $output[$key]['name'] = $key;
        $output[$key]['path'] = $path . "/$key";
        $thumbnail_url = $this->get_thumbnail_from_path($path . "/$key");
        if(!empty($thumbnail_url)){
          $output[$key]['thumbnails'] = array($thumbnail_url);
        }
        if(is_array($val) && !empty($val)){
          $output[$key]['items'] = $this->hierarchical_path_array_to_api_array($val, $path."/$key");
        }
      }
      return $output;
    }

    public function get_thumbnail_from_path($path)
    {
      // $path: /Wikipedia -english/Mathematics/Discrete mathematics
      // getcwd(): /Users/f3wztbn/gitlab_repos/TechNikh/open-curricula
      // output_dev: open-curricula-files/_articles/Wikipedia -english/Mathematics/Discrete mathematics/thumb.ext
      // thumb.png or thumb.jpg

      if(file_exists("source/open-curricula-files/_articles{$path}/thumb.png")){
        return "/open-curricula-files/_articles{$path}/thumb.png";
      }elseif(file_exists("source/open-curricula-files/_articles{$path}/thumb.jpg")){
        return "/open-curricula-files/_articles{$path}/thumb.jpg";
      }elseif(file_exists("source/open-curricula-files/_videos{$path}/thumb.png")){
        return "/open-curricula-files/_videos{$path}/thumb.png";
      }elseif(file_exists("source/open-curricula-files/_videos{$path}/thumb.jpg")){
        return "/open-curricula-files/_videos{$path}/thumb.jpg";
      }

      return '';
    }
}
