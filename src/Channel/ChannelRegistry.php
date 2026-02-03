<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Channel;

use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;

class ChannelRegistry
{
    public function __construct(private readonly iterable $channels)
    {
    }

    public function addChannel(ChannelInterface $channel): void
    {
        $this->channels->add($channel);
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

    public function getChannels(): iterable
    {
        return $this->channels;
    }
}
