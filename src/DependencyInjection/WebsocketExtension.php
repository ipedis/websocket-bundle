<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\DependencyInjection;

use Ipedis\Bundle\Websocket\Channel\ChannelRegistry;
use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class WebsocketExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        /** @var array{websocket_host: string, websocket_port: int} $connection */
        $connection = $config['connection'];
        $container->setParameter('ipedis_websocket', $connection);
        $container->setParameter('websocket_host', $connection['websocket_host']);
        $container->setParameter('websocket_port', $connection['websocket_port']);

        $yamlFileLoader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../Resources/config')
        );

        $yamlFileLoader->load('services.yaml');

        $this->addWebsocketChannelTag($container);
        $this->injectTaggedChannelService($container);
    }

    public function getAlias(): string
    {
        return 'ipedis_websocket';
    }

    protected function injectTaggedChannelService(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(ChannelRegistry::class);
        $taggedWorkers = $container->findTaggedServiceIds('ps.websocket_channel');
        foreach (array_keys($taggedWorkers) as $id) {
            $definition->addMethodCall('addChannel', [new Reference($id)]);
        }
    }

    /**
     * Automatically add tag ps.websocket_channel for all class which implement ChannelInterface.
     */
    protected function addWebsocketChannelTag(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->registerForAutoconfiguration(ChannelInterface::class)
            ->addTag('ps.websocket_channel');
    }
}
