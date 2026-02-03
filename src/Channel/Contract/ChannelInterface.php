<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Channel\Contract;

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
     * Executed when a message is published from a subscriber.
     */
    public function onPublish(Conn $conn, Topic $topic, array $payload): void;

    /**
     * Executed when a subscriber joins a channel.
     */
    public function onSubscribe(Conn $conn, Topic $topic): void;

    /**Executed when user close connection
     *
     * @param Conn $connection
     */
    public function onClose(Conn $connection): void;
}
