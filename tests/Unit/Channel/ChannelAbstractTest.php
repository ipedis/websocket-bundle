<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Unit\Channel;

use Exception;
use Ipedis\Bundle\Websocket\Channel\ChannelAbstract;
use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Tests\Stub\FakeWampConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

final class ChannelAbstractTest extends TestCase
{
    #[Test]
    public function persist_topic_stores_topic_and_enables_broadcast(): void
    {
        $channel = $this->createConcreteChannel();
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('test/123');
        $topic->expects($this->once())
            ->method('broadcast')
            ->with($this->isString());

        $channel->persistTopic('test/123', $topic);
        $channel->broadcast('test/123', ['key' => 'value']);
    }

    #[Test]
    public function broadcast_does_nothing_when_topic_not_persisted(): void
    {
        $this->expectNotToPerformAssertions();

        $channel = $this->createConcreteChannel();

        // Should not throw — just silently skip
        $channel->broadcast('unknown/topic', ['data' => 'test']);
    }

    #[Test]
    public function broadcast_sends_success_status_by_default(): void
    {
        $channel = $this->createConcreteChannel();
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('t/1');

        $broadcasted = null;
        $topic->expects($this->once())
            ->method('broadcast')
            ->willReturnCallback(function (string $json) use (&$broadcasted): void {
                $broadcasted = json_decode($json, true);
            });

        $channel->persistTopic('t/1', $topic);
        $channel->broadcast('t/1', ['foo' => 'bar']);

        $this->assertIsArray($broadcasted);
        $this->assertSame('success', $broadcasted['status']);
        $this->assertSame(['foo' => 'bar'], $broadcasted['data']);
        $this->assertSame(['topic' => 't/1'], $broadcasted['meta']);
    }

    #[Test]
    public function broadcast_sends_error_status_when_flagged(): void
    {
        $channel = $this->createConcreteChannel();
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('t/1');

        $broadcasted = null;
        $topic->expects($this->once())
            ->method('broadcast')
            ->willReturnCallback(function (string $json) use (&$broadcasted): void {
                $broadcasted = json_decode($json, true);
            });

        $channel->persistTopic('t/1', $topic);
        $channel->broadcast('t/1', ['err' => 'fail'], true);

        $this->assertIsArray($broadcasted);
        $this->assertSame('error', $broadcasted['status']);
    }

    #[Test]
    public function broadcast_preserves_custom_status_from_info(): void
    {
        $channel = $this->createConcreteChannel();
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('t/1');

        $broadcasted = null;
        $topic->expects($this->once())
            ->method('broadcast')
            ->willReturnCallback(function (string $json) use (&$broadcasted): void {
                $broadcasted = json_decode($json, true);
            });

        $channel->persistTopic('t/1', $topic);
        $channel->broadcast('t/1', ['status' => 'partial', 'progress' => 50]);

        $this->assertIsArray($broadcasted);
        $this->assertSame('partial', $broadcasted['status']);
    }

    #[Test]
    public function reply_sends_crafted_message_to_single_connection(): void
    {
        $channel = $this->createConcreteChannel();

        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('reply/topic');

        $fakeWampConnection = new FakeWampConnection();

        $channel->reply($fakeWampConnection, $topic, ['hello' => 'world']);

        $this->assertCount(1, $fakeWampConnection->calls);
        $this->assertSame('event', $fakeWampConnection->calls[0]['method']);
        $this->assertSame('reply/topic', $fakeWampConnection->calls[0]['args'][0]);

        $this->assertIsString($fakeWampConnection->calls[0]['args'][1]);
        $sentPayload = json_decode($fakeWampConnection->calls[0]['args'][1], true);
        $this->assertIsArray($sentPayload);
        $this->assertSame('success', $sentPayload['status']);
        $this->assertSame(['hello' => 'world'], $sentPayload['data']);
    }

    #[Test]
    public function on_close_does_nothing_by_default(): void
    {
        $this->expectNotToPerformAssertions();

        $channel = $this->createConcreteChannel();
        $conn = $this->createStub(ConnectionInterface::class);

        $channel->onClose($conn);
    }

    #[Test]
    public function on_error_does_nothing_by_default(): void
    {
        $this->expectNotToPerformAssertions();

        $channel = $this->createConcreteChannel();
        $conn = $this->createStub(ConnectionInterface::class);

        $channel->onError($conn, new Exception('test'));
    }

    private function createConcreteChannel(): ChannelAbstract&ChannelInterface
    {
        return new class () extends ChannelAbstract implements ChannelInterface {
            public function getBasePattern(): string
            {
                return 'test/.*';
            }

            public function onPublish(ConnectionInterface $conn, Topic $topic, array $payload): void
            {
            }

            public function onSubscribe(ConnectionInterface $conn, Topic $topic): void
            {
            }
        };
    }
}
