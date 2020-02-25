<?php


namespace Ipedis\Bundle\Websocket\Channel;

use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;

class ChannelRegistry
{
    /** @var iterable */
    private $channels;

    public function __construct(iterable $channels)
    {
        $this->channels = $channels;
    }

    public function addChannel(ChannelInterface $channel)
    {
        $this->channels->add($channel);
    }

    /**
     * Get particular channel who match a particular pattern
     * @param string $pattern
     * @return ChannelInterface
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
     * @return iterable
     */
    public function getChannels()
    {
        return $this->channels;
    }
}
