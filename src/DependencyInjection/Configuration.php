<?php

namespace Jellydock\JellypdfBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('jellypdf');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('binary_path')
                    ->defaultValue('/usr/local/bin/jellypdf-cli')
                    ->info('Path to jellypdf-cli binary')
                ->end()
                ->scalarNode('default_format')
                    ->defaultValue('A4')
                    ->info('Default paper format')
                ->end()
                ->scalarNode('default_engine')
                    ->defaultValue('puppeteer')
                    ->info('Default PDF rendering engine')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}