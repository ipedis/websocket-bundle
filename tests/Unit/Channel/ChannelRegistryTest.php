<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Unit\Channel;

use Ipedis\Bundle\Websocket\Channel\ChannelRegistry;
use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelRegistryTest extends TestCase
{
    #[Test]
    public function it_returns_empty_channels_initially(): void
    {
        $channelRegistry = new ChannelRegistry([]);

        $this->assertEmpty($channelRegistry->getChannels());
    }

    #[Test]
    public function it_accepts_channels_via_constructor(): void
    {
        $channel = $this->createMockChannel('foo/.*');

        $channelRegistry = new ChannelRegistry([$channel]);

        $this->assertCount(1, $channelRegistry->getChannels());
        $this->assertSame($channel, $channelRegistry->getChannels()[0]);
    }

    #[Test]
    public function it_adds_channel_dynamically(): void
    {
        $channelRegistry = new ChannelRegistry([]);
        $channel = $this->createMockChannel('bar/.*');

        $channelRegistry->addChannel($channel);

        $this->assertCount(1, $channelRegistry->getChannels());
        $this->assertSame($channel, $channelRegistry->getChannels()[0]);
    }

    #[Test]
    public function it_accumulates_channels_from_constructor_and_add(): void
    {
        $channel1 = $this->createMockChannel('one/.*');
        $channel2 = $this->createMockChannel('two/.*');

        $channelRegistry = new ChannelRegistry([$channel1]);
        $channelRegistry->addChannel($channel2);

        $this->assertCount(2, $channelRegistry->getChannels());
    }

    #[Test]
    public function it_returns_matching_channel_for_pattern(): void
    {
        $channel = $this->createMockChannel('notifications/.*');
        $channelRegistry = new ChannelRegistry([$channel]);

        $result = $channelRegistry->getChannelForPattern('notifications/user-123');

        $this->assertSame($channel, $result);
    }

    #[Test]
    public function it_returns_first_matching_channel_when_multiple_match(): void
    {
        $broad = $this->createMockChannel('.*');
        $specific = $this->createMockChannel('notifications/.*');

        $channelRegistry = new ChannelRegistry([$specific, $broad]);

        $result = $channelRegistry->getChannelForPattern('notifications/user-123');

        $this->assertSame($specific, $result);
    }

    #[Test]
    public function it_throws_when_no_channel_matches_pattern(): void
    {
        $channel = $this->createMockChannel('notifications/.*');
        $channelRegistry = new ChannelRegistry([$channel]);

        $this->expectException(ChannelNotFoundException::class);
        $this->expectExceptionMessage('Channel with pattern unknown/topic not found');

        $channelRegistry->getChannelForPattern('unknown/topic');
    }

    #[Test]
    public function it_throws_on_empty_registry(): void
    {
        $channelRegistry = new ChannelRegistry([]);

        $this->expectException(ChannelNotFoundException::class);

        $channelRegistry->getChannelForPattern('anything');
    }

    private function createMockChannel(string $pattern): ChannelInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getBasePattern')->willReturn($pattern);

        return $channel;
    }
}
