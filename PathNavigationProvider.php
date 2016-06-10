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
        $post_relative_url_array = explode('/', $post_relative_url);
        $length = count($post_relative_url_array);
        // 2 to account preceeding path: open-curricula-files/_videos in sculpin_kernel.yml
        $post_relative_url_array = array_splice($post_relative_url_array, 2, $length-3);
        for($i = 2; $i <= $length; $i++) {
          $clone_post_relative_url_array = $post_relative_url_array;
          $spliced_arr = array_splice($clone_post_relative_url_array, 0, $i);
          $spliced_relative_url = implode('/', $spliced_arr);
          $arr[$spliced_relative_url]['posts'][$post_relative_url] = $post;
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
        
        
        
        ladybug_dump($data);
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
        $data['tn_directories'] = $hierarchical_paths;
        /*
        $data['tn_directories'][] = array("name" => "SSC",
            "items" => array(
                array("name" => "Biology",
                    "items" => array(
                        array("name" => "Nutrition",
                    
                        ),
                        array("name" => "Respiration",
                        
                        ),
                    ),
                ),
            ),
        );
        $data['tn_directories'][] = array("name" => "9th");
        */
        $this->computedData = $data;
        return $data;
    }
}
