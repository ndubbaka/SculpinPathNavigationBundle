<?php

namespace Tn\Bundle\PathNavigationBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

/**
 * Description of PathNavigationExtension
 *
 * @author jobou
 */
class PathNavigationExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * Constructor
     *
     * @param \Tn\Bundle\PathNavigationBundle\PathNavigationProvider $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Store environment to use template
     *
     * @param \Twig_Environment $environment
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Register function
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'path_navigation',
                array(
                    $this,
                    'renderPathNavigation'
                ),
                array(
                    'is_safe' => array('html')
                )
            ),
        );
    }

    /**
     * Render the date navigation html
     *
     * @param array $page
     * @param string $template
     *
     * @return string
     */
    public function renderPathNavigation($page, $template = 'path_navigation.html')
    {
        return $this->environment->render($template, array(
            'dates_posts' => $this->container->get('tn_sculpin.path_navigation.data_provider')->provideData(),
            'page' => $page,
            'permalink_factory' => $this->container->get('tn_sculpin.path_navigation.permalink_factory'),
            'current_date' => new \DateTime()
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'tn_path_navigation';
    }
}
