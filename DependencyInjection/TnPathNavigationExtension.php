<?php

namespace Tn\Bundle\PathNavigationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Sculpin Date Navigation Extension.
 *
 * @author Jonathan Bouzekri <jonathan.bouzekri@gmail.com>
 */
class TnPathNavigationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('tn_sculpin.path_navigation.permalink.mask_year', $config['permalink_year']);
        $container->setParameter('tn_sculpin.path_navigation.permalink.mask_month', $config['permalink_month']);
    }
}
