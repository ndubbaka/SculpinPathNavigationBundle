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
    
    private function processContentTypeData($dataProvider)
    {
      
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

        $data = array();
        $out = array();
        
        $all_paths = array();
        $hierarchical_paths = array();
        foreach ($this->dataProvider->provideData() as $post) {
          //print "111 Provider ".$post->sourceId()."222". "\r\n";
          //https://github.com/sculpin/sculpin/blob/master/src/Sculpin/Bundle/PaginationBundle/PaginationGenerator.php#L101
          $sourcePermalinkFactory = new SourcePermalinkFactory('');
          $permalink = $sourcePermalinkFactory->create($post);
          $post_relative_url = $permalink->relativeUrlPath();
          //print " 111 ".$post_relative_url." 222 ". "\r\n";
          $post_relative_url = trim($post_relative_url, '/');
          $post_relative_url_array = explode('/', $post_relative_url);
          $length = count($post_relative_url_array);
          $post_relative_url_array = array_splice($post_relative_url_array, 1, $length-2);
          for($i = 1; $i <= count($post_relative_url_array); $i++) {
            $clone_post_relative_url_array = $post_relative_url_array;
            $spliced_arr = array_splice($clone_post_relative_url_array, 0, $i);
            $spliced_relative_url = implode('/', $spliced_arr);
            $arr[$spliced_relative_url]['posts'][] = $post;
            $all_paths[$spliced_relative_url][] = $spliced_relative_url;
          }
          //ladybug_dump($arr);
          foreach ($arr as $key=>$val) {
            $r = & $data;
            foreach (explode("/", $key) as $key) {
              if (!isset($r[$key])) {
                $r[$key] = array();
              }
              $r = & $r[$key];
            }
            $r = $val;
            
       
            
            //var_export($data);
            //print "------------999 Provider ".print_r($val, TRUE)."888". "\r\n";
          }
          //ladybug_dump($data);
          //break;
          
          //print "111 Provider ".print_r($out, TRUE)."222". "\r\n";
          //$data = $out;
          
          /*$data['dir1']['posts'][] = "nikhil";
          $data['dir1']['months']['dir11']['posts'][] = "nikhil";*/
          //var_dump($data);
          //break;
          /*
            $date = \DateTime::createFromFormat('U', 0);
            if ($post->date() !== "") {
                $date = \DateTime::createFromFormat('U', $post->date());
            }

            $year = $date->format('Y');
            $month = $date->format('m');
            $keyDate = \DateTime::createFromFormat('Y-m',$year.'-'.$month);

            if (!isset($data[$year])) {
                $data[$year] = array(
                    'posts' => array(),
                    'months' => array(),
                    'date' => $keyDate
                );
            }

            if (!isset($data[$year]['months'])) {
                $data[$year]['months'] = array('posts' => array(), 'date' => $keyDate);
            }

            $data[$year]['posts'][] = $post;
            $data[$year]['months'][$month]['posts'][] = $post;*/
        }
        ladybug_dump($all_paths);
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
        ladybug_dump($hierarchical_paths);
        //print "111 Provider ".print_r($data, TRUE)."222". "\r\n";
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
        //var_dump(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        return $data;
    }
}
