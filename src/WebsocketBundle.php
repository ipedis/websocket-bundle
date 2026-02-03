<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket;

use Ipedis\Bundle\Websocket\DependencyInjection\WebsocketExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebsocketBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if (null === $this->extension) {
            $this->extension = new WebsocketExtension();
        }

        return $this->extension;
    }
}
