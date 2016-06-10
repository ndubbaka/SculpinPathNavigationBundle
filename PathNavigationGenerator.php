<?php

namespace Tn\Bundle\PathNavigationBundle;

use Sculpin\Core\Generator\GeneratorInterface;
use Sculpin\Core\Source\SourceInterface;
use Sculpin\Core\Permalink\Permalink;
use Sculpin\Core\Permalink\SourcePermalinkFactory;
use Sculpin\Core\DataProvider\DataProviderInterface;
use Tn\Bundle\PathNavigationBundle\Permalink\PermalinkFactory;

/**
 * PathNavigationGenerator
 *
 * @author Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 */
class PathNavigationGenerator implements GeneratorInterface
{
    /**
     * @var \Sculpin\Core\DataProvider\DataProviderInterface
     */
    private $dataProvider;
    private $articlesDataProvider;

    /**
     *
     * @var \Tn\Bundle\PathNavigationBundle\PathNavigationProvider
     */
    private $PathNavigationProvider;

    /**
     * @var \Tn\Bundle\PathNavigationBundle\Permalink\PermalinkFactory
     */
    private $permalinkFactory;

    /**
     * Constructor
     *
     * @param \Sculpin\Core\DataProvider\DataProviderInterface $dataProviderManager
     * @param \Tn\Bundle\PathNavigationBundle\PathNavigationProvider $PathNavigationProvider
     */
    public function __construct(
        DataProviderInterface $dataProviderManager,
        DataProviderInterface $articlesDataProviderManager,
        PathNavigationProvider $PathNavigationProvider,
        PermalinkFactory $permalinkFactory
    ) {
        $this->dataProvider = $dataProviderManager;
        $this->articlesDataProvider = $articlesDataProviderManager;
        $this->PathNavigationProvider = $PathNavigationProvider;
        $this->permalinkFactory = $permalinkFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SourceInterface $source)
    {
        $generatedPaths = array();
        //$generatedMonths = array();
        $generatedSources = array();

        $datedPostData = $this->PathNavigationProvider->provideData();
        //print_r($datedPostData['tn_directories']);
        //print "444".print_r($datedPostData['tn_directories'], TRUE)."555". "\r\n";
        foreach ($this->dataProvider->provideData() as $post) {
          // Get post URL Path
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
            //$arr[$spliced_relative_url]['posts'][] = "nikhil";
            
            $pathGeneratedSource = $source->duplicate(
                $source->sourceId().':path='."api/v1/categories//$spliced_relative_url/"
                );
            $pathGeneratedSource->data()->set('permalink', "api/v1/categories/$spliced_relative_url/");
            $pathGeneratedSource->data()->set('path', "api/v1/categories/$spliced_relative_url/");
            
            $post_data_url_array = explode('/', $spliced_relative_url);
            $clone_datedPostData = $datedPostData;
            foreach($post_data_url_array as $directory_name){
              $clone_datedPostData = $clone_datedPostData[$directory_name];
            }
            $pathGeneratedSource->data()->set('path_posts', $clone_datedPostData['posts']);
            
            $generatedPaths[] = "api/v1/categories/$spliced_relative_url/";
            $generatedSources[] = $pathGeneratedSource;
          }
          
          
          
          
          /*$pathGeneratedSource = $source->duplicate(
              $source->sourceId().':path='.'blog/dir1/'
              );
          $pathGeneratedSource->data()->set('permalink', 'blog/dir1/');
          $pathGeneratedSource->data()->set('path', 'blog/dir1/');
          $pathGeneratedSource->data()->set('path_posts', $datedPostData['dir1']['posts']);
          $generatedPaths[] = 'blog/dir1/';
          $generatedSources[] = $pathGeneratedSource;*/
          
            /*$date = \DateTime::createFromFormat('U', 0);
            if ($post->date() !== "") {
                $date = \DateTime::createFromFormat('U', $post->date());
            }

            $year = $date->format('Y');
            $month = $date->format('m');

            if (in_array($year.'-'.$month, $generatedMonths)) {
                continue;
            }

            $monthGeneratedSource = $source->duplicate(
                $source->sourceId().':year='.$year.':month='.$month
            );
            $monthGeneratedSource->data()->set('permalink', $this->permalinkFactory->getMonth($year, $month));
            $monthGeneratedSource->data()->set('year', $year);
            $monthGeneratedSource->data()->set('month', $month);
            $monthGeneratedSource->data()->set('path_posts', $datedPostData[$year]['months'][$month]['posts']);
            $generatedMonths[] = $year.'-'.$month;
            $generatedSources[] = $monthGeneratedSource;

            if (in_array($year, $generatedYears)) {
                continue;
            }

            $yearGeneratedSource = $source->duplicate(
                $source->sourceId().':year='.$year
            );
            $yearGeneratedSource->data()->set('permalink', $this->permalinkFactory->getYear($year));
            $yearGeneratedSource->data()->set('year', $year);
            $yearGeneratedSource->data()->set('path_posts', $datedPostData[$year]['posts']);
            $generatedYears[] = $year;
            $generatedSources[] = $yearGeneratedSource;*/
        }

        return $generatedSources;
    }
}
