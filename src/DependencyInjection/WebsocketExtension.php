<?php
namespace Ipedis\Bundle\Websocket\DependencyInjection;

use Ipedis\Bundle\Websocket\Channel\ChannelRegistry;
use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Service\Topic\TopicManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class WebsocketExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('ipedis_websocket', $config['connection']);
        $container->setParameter('websocket_host', $config['connection']['websocket_host']);
        $container->setParameter('websocket_port', $config['connection']['websocket_port']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../Resources/config')
        );

        $loader->load('services.yaml');

        $this->addWebsocketChannelTag($container);
        $this->injectTaggedChannelService($container);
    }

    public function getAlias()
    {
        return 'ipedis_websocket';
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function injectTaggedChannelService(ContainerBuilder $container)
    {
        $definition = $container->findDefinition(ChannelRegistry::class);
        $taggedWorkers = $container->findTaggedServiceIds('ps.websocket_channel');
        foreach ($taggedWorkers as $id => $tags) {
            $definition->addMethodCall('addChannel', [new Reference($id)]);
        }
    }

    /**
     * Automatically add tag ps.websocket_channel for all class which implement ChannelInterface
     * @param ContainerBuilder $containerBuilder
     */
    protected function addWebsocketChannelTag(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->registerForAutoconfiguration(ChannelInterface::class)
            ->addTag('ps.websocket_channel');
    }
}
