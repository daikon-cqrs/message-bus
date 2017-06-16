<?php

namespace Accordia\MessageBus\Channel;

use Accordia\DataStructures\TypedMapTrait;

final class ChannelMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    /**
     * @param ChannelInterface[] $channels
     */
    public function __construct(array $channels = [])
    {
        $this->init(array_reduce($channels, function (array $carry, ChannelInterface $channel) {
            $carry[$channel->getKey()] = $channel; // enforce consistent channel keys
            return $carry;
        }, []), ChannelInterface::CLASS);
    }
}
