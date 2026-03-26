<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Unit\Service\Topic;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ipedis\Bundle\Websocket\Channel\ChannelRegistry;
use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;
use Ipedis\Bundle\Websocket\Service\Logger\WebsocketEventLogger;
use Ipedis\Bundle\Websocket\Service\Topic\TopicManager;
use Ipedis\Bundle\Websocket\Tests\Stub\FakeWampConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

final class TopicManagerTest extends TestCase
{
    private ChannelRegistry&MockObject $registry;

    private WebsocketEventLogger&MockObject $logger;

    private TopicManager $manager;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ChannelRegistry::class);
        $this->logger = $this->createMock(WebsocketEventLogger::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $connection = $this->createMock(Connection::class);
        $connection->method('isConnected')->willReturn(true);
        $em->method('getConnection')->willReturn($connection);

        $this->manager = new TopicManager($this->registry, $this->logger, $em);
    }

    #[Test]
    public function on_subscribe_delegates_to_matching_channel(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('notifications/123');
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())->method('persistTopic')->with('notifications/123', $topic);
        $channel->expects($this->once())->method('onSubscribe')->with($conn, $topic);

        $this->registry->method('getChannelForPattern')->with('notifications/123')->willReturn($channel);

        $this->manager->onSubscribe($conn, $topic);
    }

    #[Test]
    public function on_subscribe_ignores_string_topic(): void
    {
        $conn = $this->createStub(ConnectionInterface::class);

        $this->registry->expects($this->never())->method('getChannelForPattern');

        $this->manager->onSubscribe($conn, 'string-topic');
    }

    #[Test]
    public function on_subscribe_logs_error_when_channel_not_found(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('unknown/topic');
        $conn = $this->createStub(ConnectionInterface::class);

        $this->registry->method('getChannelForPattern')
            ->willThrowException(new ChannelNotFoundException('not found'));

        $this->logger->expects($this->atLeastOnce())->method('writeError')->with('not found');

        $this->manager->onSubscribe($conn, $topic);
    }

    #[Test]
    public function on_publish_handles_ping_topic(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('ping');
        $fakeWampConnection = new FakeWampConnection();

        $this->registry->expects($this->never())->method('getChannelForPattern');

        $this->manager->onPublish($fakeWampConnection, $topic, 'anything', [], []);

        $this->assertCount(1, $fakeWampConnection->calls);
        $this->assertSame('event', $fakeWampConnection->calls[0]['method']);
        $this->assertSame('ping', $fakeWampConnection->calls[0]['args'][0]);

        $this->assertIsString($fakeWampConnection->calls[0]['args'][1]);
        $decoded = json_decode($fakeWampConnection->calls[0]['args'][1], true);
        $this->assertIsArray($decoded);
        $this->assertSame('pong', $decoded['message']);
    }

    #[Test]
    public function on_publish_forwards_array_event_to_channel(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('chat/room-1');
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())
            ->method('onPublish')
            ->with($conn, $topic, ['msg' => 'hello']);

        $this->registry->method('getChannelForPattern')->willReturn($channel);

        $this->manager->onPublish($conn, $topic, ['msg' => 'hello'], [], []);
    }

    #[Test]
    public function on_publish_decodes_json_string_event(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('chat/room-1');
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())
            ->method('onPublish')
            ->with($conn, $topic, ['msg' => 'hi']);

        $this->registry->method('getChannelForPattern')->willReturn($channel);

        $this->manager->onPublish($conn, $topic, json_encode(['msg' => 'hi']), [], []);
    }

    #[Test]
    public function on_publish_handles_non_json_string_event(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('chat/room-1');
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())
            ->method('onPublish')
            ->with($conn, $topic, []);

        $this->registry->method('getChannelForPattern')->willReturn($channel);

        $this->manager->onPublish($conn, $topic, 'not-json', [], []);
    }

    #[Test]
    public function on_publish_handles_non_array_non_string_event(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('chat/room-1');
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())
            ->method('onPublish')
            ->with($conn, $topic, []);

        $this->registry->method('getChannelForPattern')->willReturn($channel);

        $this->manager->onPublish($conn, $topic, 42, [], []);
    }

    #[Test]
    public function on_publish_ignores_string_topic(): void
    {
        $conn = $this->createStub(ConnectionInterface::class);

        $this->registry->expects($this->never())->method('getChannelForPattern');

        $this->manager->onPublish($conn, 'string-topic', [], [], []);
    }

    #[Test]
    public function on_publish_logs_error_when_channel_not_found(): void
    {
        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('unknown/topic');
        $conn = $this->createStub(ConnectionInterface::class);

        $this->registry->method('getChannelForPattern')
            ->willThrowException(new ChannelNotFoundException('not found'));

        $this->logger->expects($this->atLeastOnce())->method('writeError');

        $this->manager->onPublish($conn, $topic, [], [], []);
    }

    #[Test]
    public function on_error_delegates_to_all_channels_and_logs(): void
    {
        $conn = $this->createStub(ConnectionInterface::class);
        $exception = new Exception('something broke');

        $channel1 = $this->createMock(ChannelInterface::class);
        $channel1->expects($this->once())->method('onError')->with($conn, $exception);

        $channel2 = $this->createMock(ChannelInterface::class);
        $channel2->expects($this->once())->method('onError')->with($conn, $exception);

        $this->registry->method('getChannels')->willReturn([$channel1, $channel2]);
        $this->logger->expects($this->once())->method('writeError')
            ->with('Websocket got error: something broke');

        $this->manager->onError($conn, $exception);
    }

    #[Test]
    public function on_call_returns_error(): void
    {
        $fakeWampConnection = new FakeWampConnection();
        $topic = $this->createStub(Topic::class);

        $this->manager->onCall($fakeWampConnection, 'rpc-1', $topic, []);

        $this->assertCount(1, $fakeWampConnection->calls);
        $this->assertSame('callError', $fakeWampConnection->calls[0]['method']);
        $this->assertSame('rpc-1', $fakeWampConnection->calls[0]['args'][0]);
        $this->assertSame($topic, $fakeWampConnection->calls[0]['args'][1]);
        $this->assertSame('RPC not supported', $fakeWampConnection->calls[0]['args'][2]);
    }

    #[Test]
    public function on_open_does_nothing(): void
    {
        $this->expectNotToPerformAssertions();

        $conn = $this->createStub(ConnectionInterface::class);

        $this->manager->onOpen($conn);
    }

    #[Test]
    public function on_close_delegates_to_all_channels(): void
    {
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createMock(ChannelInterface::class);
        $channel->expects($this->once())->method('onClose')->with($conn);

        $this->registry->method('getChannels')->willReturn([$channel]);

        $this->manager->onClose($conn);
    }

    #[Test]
    public function on_unsubscribe_does_nothing(): void
    {
        $this->expectNotToPerformAssertions();

        $conn = $this->createStub(ConnectionInterface::class);
        $topic = $this->createStub(Topic::class);

        $this->manager->onUnSubscribe($conn, $topic);
    }

    #[Test]
    public function refresh_db_reconnects_when_disconnected(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('isConnected')->willReturn(false);
        $connection->expects($this->once())->method('close');
        $connection->expects($this->once())->method('connect');

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($connection);

        $topicManager = new TopicManager($this->registry, $this->logger, $em);

        $topic = $this->createMock(Topic::class);
        $topic->method('getId')->willReturn('test/1');
        $conn = $this->createStub(ConnectionInterface::class);

        $channel = $this->createStub(ChannelInterface::class);
        $this->registry->method('getChannelForPattern')->willReturn($channel);

        // Trigger refreshDbConnection via onSubscribe
        $topicManager->onSubscribe($conn, $topic);
    }
}
