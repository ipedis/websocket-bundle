<?php
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
     *
     * @return string
     */
    public function getBasePattern(): string;

    /**
     * Executed when a message is published from a subscriber.
     *
     * @param Conn  $conn
     * @param Topic $topic
     * @param array $payload
     *
     * @return mixed
     */
    public function onPublish(Conn $conn, Topic $topic, array $payload);

    /**
     * Executed when a subscriber joins a channel.
     *
     * @param Conn  $conn
     * @param Topic $topic
     *
     * @return mixed
     */
    public function onSubscribe(Conn $conn, Topic $topic);

    /**Executed when user close connection
     *
     * @param Conn $connection
     * @return mixed
     */
    public function onClose(Conn $connection);
}
