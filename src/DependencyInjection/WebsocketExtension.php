<?php
namespace Ipedis\Bundle\Websocket\DependencyInjection;

use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../Resources/config')
        );

        $loader->load('services.yaml');
        $this->addWebsocketChannelTag($container);
    }

    public function getAlias()
    {
        return 'ipedis_websocket';
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
