<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Unit;

use Ipedis\Bundle\Websocket\DependencyInjection\WebsocketExtension;
use Ipedis\Bundle\Websocket\WebsocketBundle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

final class WebsocketBundleTest extends TestCase
{
    #[Test]
    public function bundle_can_be_instantiated(): void
    {
        $websocketBundle = new WebsocketBundle();

        $this->assertInstanceOf(WebsocketBundle::class, $websocketBundle);
    }

    #[Test]
    public function get_container_extension_returns_websocket_extension(): void
    {
        $websocketBundle = new WebsocketBundle();

        $extension = $websocketBundle->getContainerExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        $this->assertInstanceOf(WebsocketExtension::class, $extension);
    }

    #[Test]
    public function get_container_extension_returns_same_instance(): void
    {
        $websocketBundle = new WebsocketBundle();

        $first = $websocketBundle->getContainerExtension();
        $second = $websocketBundle->getContainerExtension();

        $this->assertSame($first, $second);
    }

    #[Test]
    public function extension_alias_is_ipedis_websocket(): void
    {
        $websocketBundle = new WebsocketBundle();
        $extension = $websocketBundle->getContainerExtension();

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        $this->assertSame('ipedis_websocket', $extension->getAlias());
    }
}
