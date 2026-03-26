<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Channel\Contract;

use Exception;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\Topic;

/**
 * Interface ChannelInterface.
 */
interface ChannelInterface
{
    /**
     * The base pattern to identify the channel.
     */
    public function getBasePattern(): string;

    /**
     * Track topic.
     */
    public function persistTopic(string $id, Topic $topic): void;

    /**
     * Executed when a message is published from a subscriber.
     *
     * @param array<string, mixed> $payload
     */
    public function onPublish(Conn $conn, Topic $topic, array $payload): void;

    /**
     * Executed when a subscriber joins a channel.
     */
    public function onSubscribe(Conn $conn, Topic $topic): void;

    /**
     * Executed when user close connection.
     */
    public function onClose(Conn $connection): void;

    /**
     * Executed when an error occurs.
     */
    public function onError(Conn $connection, Exception $exception): void;
}
