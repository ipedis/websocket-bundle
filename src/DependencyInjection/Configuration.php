<?php


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ipedis_websocket');
        $treeBuilder->getRootNode()
            ->children()
               ->arrayNode('connection')
                  ->children()
                      ->scalarNode('websocket_remote_host')->defaultValue('localhost')->end()
                      ->integerNode('websocket_remote_port')->defaultValue(8081)->end()
                      ->scalarNode('websocket_host')->defaultValue('127.0.0.1')->end()
                      ->integerNode('websocket_port')->defaultValue(8081)->end()
                      ->scalarNode('websocket_remote_protocol')->defaultValue('ws')->end()
                  ->end()
               ->end()
            ->end();

        return $treeBuilder;
    }
}
