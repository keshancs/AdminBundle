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
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('label')->end()
                                    ->arrayNode('items')
                                        ->useAttributeAsKey('code')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('label')->end()
                                                ->scalarNode('icon')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('items')
                            ->useAttributeAsKey('code')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('label')->end()
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