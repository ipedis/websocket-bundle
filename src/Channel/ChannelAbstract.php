<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Channel;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;

/**
 * Class ChannelAbstract.
 *
 * @phpstan-type PayloadArray array{status: string, data: array<string, mixed>, meta: array{topic: string}}
 */
abstract class ChannelAbstract
{
    /** @var array<string, Topic> */
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
     *
     * @param array<string, mixed> $info
     */
    public function broadcast(string $topicId, array $info, bool $isError = false): void
    {
        if ($this->hasTopic($topicId)) {
            $payload = $this->craftMessage($topicId, $info, $isError);

            $this->getTopic($topicId)->broadcast(json_encode($payload) ?: '');
        }
    }

    /**
     * Reply single subscriber with a message.
     *
     * @param array<string, mixed> $payload
     */
    public function reply(ConnectionInterface $conn, Topic $topic, array $payload): void
    {
        $payload = $this->craftMessage($topic->getId(), $payload);

        /** @phpstan-ignore method.notFound */
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
     *
     * @param array<string, mixed> $info
     * @return array<string, mixed>
     */
    protected function craftMessage(string $topicId, array $info, bool $isError = false): array
    {
        $payload = [];

        if (!empty($info['status'])) {
            $payload['status'] = $info['status'];
        } else {
            $payload['status'] = ($isError) ? 'error' : 'success';
        }

        $payload['data'] = $info;
        $payload['meta'] = ['topic' => $topicId];

        return $payload;
    }

    /**
     * Pattern match target.
     */
    protected function hasMatch(string $pattern, string $target): bool
    {
        return (bool) preg_match(sprintf('#%s#', $pattern), $target);
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
