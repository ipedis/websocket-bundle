<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Service\Topic;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ipedis\Bundle\Websocket\Channel\ChannelRegistry;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;
use Ipedis\Bundle\Websocket\Service\Logger\WebsocketEventLogger;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;

class TopicManager implements WampServerInterface
{
    public const PING_TOPIC = 'ping';

    public function __construct(protected ChannelRegistry $registry, protected WebsocketEventLogger $logger, protected EntityManagerInterface $em)
    {
    }

    /**
     * A request to subscribe to a topic has been made.
     *
     * @param string|Topic $topic The topic to subscribe to
     */
    public function onSubscribe(ConnectionInterface $conn, $topic): void
    {
        if (!$topic instanceof Topic) {
            return;
        }

        $this->refreshDbConnection();

        $this->logger->writeInfo(sprintf('Received subscribe request for topic {%s}', $topic->getId()));
        try {
            $channel = $this->registry->getChannelForPattern($topic->getId());
            $channel->persistTopic($topic->getId(), $topic);
            $channel->onSubscribe($conn, $topic);
        } catch (ChannelNotFoundException $channelNotFoundException) {
            $this->logger->writeError($channelNotFoundException->getMessage());
        }
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI.
     *
     * @param string|Topic $topic The topic the user has attempted to publish to
     * @param mixed $event Payload of the publish
     * @param array<string> $exclude A list of session IDs the message should be excluded from (blacklist)
     * @param array<string> $eligible A list of session Ids the message should be send to (whitelist)
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible): void
    {
        if (!$topic instanceof Topic) {
            return;
        }

        $this->refreshDbConnection();

        $this->logger->writeInfo(sprintf('Received publish request for topic {%s}', $topic->getId()));

        if (self::PING_TOPIC === $topic->getId()) {
            /** @phpstan-ignore method.notFound */
            $conn->event($topic->getId(), json_encode(['message' => 'pong']));

            return;
        }

        if (is_array($event)) {
            /** @var array<string, mixed> $payload */
            $payload = $event;
        } elseif (is_string($event)) {
            $decoded = json_decode($event, true);
            /** @var array<string, mixed> $payload */
            $payload = is_array($decoded) ? $decoded : [];
        } else {
            $payload = [];
        }

        try {
            $channel = $this->registry->getChannelForPattern($topic->getId());
            $channel->onPublish($conn, $topic, $payload);
        } catch (ChannelNotFoundException $channelNotFoundException) {
            $this->logger->writeError($channelNotFoundException->getMessage());
        }
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method.
     *
     * @throws Exception
     */
    public function onError(ConnectionInterface $conn, Exception $e): void
    {
        foreach ($this->registry->getChannels() as $channel) {
            $channel->onError($conn, $e);
        }

        $this->logger->writeError(sprintf('Websocket got error: %s', $e->getMessage()));
    }

    /**
     * An RPC call has been received.
     *
     * @param string $id The unique ID of the RPC, required to respond to
     * @param string|Topic $topic The topic to execute the call against
     * @param array<mixed> $params Call parameters received from the client
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params): void
    {
        /** @phpstan-ignore method.notFound */
        $conn->callError($id, $topic, 'RPC not supported');
    }

    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param ConnectionInterface $conn The socket/connection that just connected to your application
     *
     * @throws Exception
     */
    public function onOpen(ConnectionInterface $conn): void
    {
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws Exception
     */
    public function onClose(ConnectionInterface $conn): void
    {
        foreach ($this->registry->getChannels() as $channel) {
            $channel->onClose($conn);
        }
    }

    /**
     * A request to unsubscribe from a topic has been made.
     * No need to anything, since WampServer adds and removes subscribers to Topics automatically.
     *
     * @param string|Topic $topic The topic to unsubscribe from
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic): void
    {
    }

    /**
     * Refresh db connection.
     */
    protected function refreshDbConnection(): void
    {
        $this->logger->writeInfo(sprintf('Database connection status %s', $this->em->getConnection()->isConnected()));

        if (false === $this->em->getConnection()->isConnected()) {
            $this->em->getConnection()->close();
            $this->em->getConnection()->connect();

            $this->logger->writeInfo('Database connection reset');
        }
    }
}
