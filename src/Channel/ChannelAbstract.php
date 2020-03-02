<?php

namespace Ipedis\Bundle\Websocket\Channel;

use Ratchet\ConnectionInterface;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\Topic;

/**
 * Class ChannelAbstract.
 */
abstract class ChannelAbstract
{
    /**
     * @var array
     */
    protected $topics = [];

    /**
     * Track topic.
     *
     * @param string $id
     * @param Topic  $topic
     */
    public function persistTopic(string $id, Topic $topic)
    {
        $this->setTopic($id, $topic);
    }

    /**
     * Broadcast messages to subscribers based on topic id.
     *
     * @param string $topicId
     * @param array  $info
     * @param bool   $isError
     */
    public function broadcast(string $topicId, array $info, bool $isError = false)
    {
        if ($this->hasTopic($topicId)) {
            /**
             * Craft message.
             */
            $payload = $this->craftMessage($topicId, $info, $isError);

            $this->getTopic($topicId)->broadcast(json_encode($payload));
        }
    }

    /**
     * Reply single subscriber with a message.
     *
     * @param Conn  $conn
     * @param Topic $topic
     * @param array $payload
     */
    public function reply(Conn $conn, Topic $topic, array $payload)
    {
        /**
         * Craft message.
         */
        $payload = $this->craftMessage($topic->getId(), $payload);

        /*
         * Send message to single subscriber
         */
        $conn->event($topic->getId(), json_encode($payload));
    }

    /**
     * @param string $id
     *
     * @return bool
     */
    protected function hasTopic(string $id): bool
    {
        return !empty($this->topics[$id]);
    }

    /**
     * @param string $id
     *
     * @return Topic
     */
    protected function getTopic(string $id): Topic
    {
        return $this->topics[$id];
    }

    /**
     * @param string $id
     * @param Topic  $topic
     */
    protected function setTopic(string $id, Topic $topic): void
    {
        $this->topics[$id] = $topic;
    }

    /**
     * Craft message.
     *
     * @param $topicId
     * @param $info
     * @param bool $isError
     *
     * @return array
     */
    protected function craftMessage($topicId, $info, $isError = false): array
    {
        $payload = [];

        if (!empty($info['status'])) {
            $payload['status'] = $info['status'];
        } else {
            $payload['status'] = ($isError) ? 'error' : 'success';
        }

        /*
         * Payload data
         */
        $payload['data'] = $info;

        /*
         * Payload meta
         */
        $payload['meta']['topic'] = $topicId;

        return $payload;
    }

    /**
     * Pattern match target.
     *
     * @param string $pattern
     * @param string $target
     *
     * @return bool
     */
    protected function hasMatch(string $pattern, string $target): bool
    {
        return preg_match(sprintf('#%s#', $pattern), $target);
    }

    /**
     * @param Conn $connection
     */
    public function onClose(ConnectionInterface $connection)
    {
        //By default do nothing
    }

    /**
     * @param Conn $connection
     * @param \Exception $exception
     */
    public function onError(ConnectionInterface $connection, \Exception $exception)
    {
        //By default, do nothing
    }
}
