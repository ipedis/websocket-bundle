<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Channel;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\ConnectionInterface as Conn;
use Ratchet\Wamp\Topic;

/**
 * Class ChannelAbstract.
 */
abstract class ChannelAbstract
{
    protected array $topics = [];

    /**
     * Track topic.
     */
    public function persistTopic(string $id, Topic $topic): void
    {
        $this->setTopic($id, $topic);
    }

    /**
     * Broadcast messages to subscribers based on topic id.
     */
    public function broadcast(string $topicId, array $info, bool $isError = false): void
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
     */
    public function reply(Conn $conn, Topic $topic, array $payload): void
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

    protected function hasTopic(string $id): bool
    {
        return !empty($this->topics[$id]);
    }

    protected function getTopic(string $id): Topic
    {
        return $this->topics[$id];
    }

    protected function setTopic(string $id, Topic $topic): void
    {
        $this->topics[$id] = $topic;
    }

    /**
     * Craft message.
     */
    protected function craftMessage($topicId, $info, bool $isError = false): array
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
     */
    protected function hasMatch(string $pattern, string $target): bool
    {
        return preg_match(sprintf('#%s#', $pattern), $target);
    }

    public function onClose(ConnectionInterface $connection): void
    {
        // By default do nothing
    }

    public function onError(ConnectionInterface $connection, Exception $exception): void
    {
        // By default, do nothing
    }
}
