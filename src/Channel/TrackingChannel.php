<?php


namespace Ipedis\Bundle\Websocket\Channel;


use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\Topic;

class TrackingChannel extends ChannelAbstract implements ChannelInterface
{
    /**
     * @inheritDoc
     */
    public function getBasePattern(): string
    {
        // TODO: Implement getBasePattern() method.
    }

    /**
     * @inheritDoc
     */
    public function onPublish(Conn $conn, Topic $topic, array $payload)
    {
        // TODO: Implement onPublish() method.
    }

    /**
     * @inheritDoc
     */
    public function onSubscribe(Conn $conn, Topic $topic)
    {
        // TODO: Implement onSubscribe() method.
    }
}
