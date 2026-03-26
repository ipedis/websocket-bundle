<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Unit\DependencyInjection;

use Ipedis\Bundle\Websocket\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function it_provides_default_connection_values(): void
    {
        $config = $this->processConfiguration(['connection' => []]);

        $this->assertIsArray($config['connection']);
        $this->assertSame('127.0.0.1', $config['connection']['websocket_host']);
        $this->assertSame(8081, $config['connection']['websocket_port']);
    }

    #[Test]
    public function it_allows_custom_host_and_port(): void
    {
        $config = $this->processConfiguration([
            'connection' => [
                'websocket_host' => '0.0.0.0',
                'websocket_port' => 9090,
            ],
        ]);

        $this->assertIsArray($config['connection']);
        $this->assertSame('0.0.0.0', $config['connection']['websocket_host']);
        $this->assertSame(9090, $config['connection']['websocket_port']);
    }

    #[Test]
    public function it_allows_partial_override(): void
    {
        $config = $this->processConfiguration([
            'connection' => [
                'websocket_port' => 3000,
            ],
        ]);

        $this->assertIsArray($config['connection']);
        $this->assertSame('127.0.0.1', $config['connection']['websocket_host']);
        $this->assertSame(3000, $config['connection']['websocket_port']);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private function processConfiguration(array $input): array
    {
        /** @var array<string, mixed> */
        return (new Processor())->processConfiguration(new Configuration(), [$input]);
    }
}
