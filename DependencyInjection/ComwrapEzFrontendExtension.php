<?php

namespace Comwrap\Bundle\ComwrapEzFrontendBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ComwrapEzFrontendExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('Comwrap\Bundle\ComwrapEzFrontendBundle\Service\FrontendHandler');
        if(isset($config['frontend'])){
            if(isset($config['frontend']['source'])){
                $definition->replaceArgument(1, $config['frontend']['source']);
            }
            if(isset($config['frontend']['destination'])){
                $definition->replaceArgument(2, $config['frontend']['destination']);
            }
            if(isset($config['frontend']['assets'])){
                $definition->replaceArgument(3, $config['frontend']['assets']);
            }
        }
    }
}
