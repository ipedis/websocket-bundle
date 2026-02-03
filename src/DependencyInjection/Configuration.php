<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ipedis_websocket');
        $treeBuilder->getRootNode()
            ->children()
               ->arrayNode('connection')
                  ->children()
                      ->scalarNode('websocket_host')->defaultValue('127.0.0.1')->end()
                      ->integerNode('websocket_port')->defaultValue(8081)->end()
                  ->end()
               ->end()
            ->end();

        return $treeBuilder;
    }
}
