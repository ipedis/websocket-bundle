<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Channel;

use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;

class ChannelRegistry
{
    /** @var array<int, ChannelInterface> */
    private array $channels = [];

    /**
     * @param iterable<ChannelInterface> $channels
     */
    public function __construct(iterable $channels = [])
    {
        foreach ($channels as $channel) {
            $this->channels[] = $channel;
        }
    }

    public function addChannel(ChannelInterface $channel): void
    {
        $this->channels[] = $channel;
    }

    /**
     * Get particular channel who match a particular pattern.
     *
     * @throws ChannelNotFoundException
     */
    public function getChannelForPattern(string $pattern): ChannelInterface
    {
        foreach ($this->channels as $channel) {
            if (preg_match(sprintf('#%s#', $channel->getBasePattern()), $pattern)) {
                return $channel;
            }
        }

        throw new ChannelNotFoundException(sprintf('Channel with pattern %s not found', $pattern));
    }

    /**
     * @return array<int, ChannelInterface>
     */
    public function getChannels(): array
    {
        return $this->channels;
    }
}
