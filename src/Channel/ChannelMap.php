<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel;

use Daikon\DataStructures\TypedMapTrait;

final class ChannelMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $channels = [])
    {
        $this->init(array_reduce($channels, function (array $carry, ChannelInterface $channel) {
            $carry[$channel->getKey()] = $channel; // enforce consistent channel keys
            return $carry;
        }, []), ChannelInterface::CLASS);
    }
}
