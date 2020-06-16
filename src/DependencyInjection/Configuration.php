<?php

namespace AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('admin');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('menu')
                    ->children()
                        ->arrayNode('groups')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->arrayNode('items')
                                        ->prototype('array')
                                            ->children()
                                                ->scalarNode('label')->end()
                                                ->scalarNode('code')->end()
                                                ->scalarNode('icon')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('items')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->scalarNode('code')->end()
                                    ->scalarNode('icon')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}