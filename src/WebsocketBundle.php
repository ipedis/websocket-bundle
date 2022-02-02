<?php
namespace Ipedis\Bundle\Websocket;

use Ipedis\Bundle\Websocket\DependencyInjection\WebsocketExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebsocketBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new WebsocketExtension();
        }

        return $this->extension;
    }
}
