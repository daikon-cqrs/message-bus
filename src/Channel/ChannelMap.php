<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel;

use Countable;
use Daikon\DataStructure\TypedMapTrait;
use InvalidArgumentException;
use IteratorAggregate;

final class ChannelMap implements IteratorAggregate, Countable
{
    use TypedMapTrait;

    /** @param ChannelInterface[] $channels */
    public function __construct(array $channels = [])
    {
        $this->init(array_reduce($channels, function (array $carry, ChannelInterface $channel): array {
            $channelKey = $channel->getKey();
            if (isset($carry[$channelKey])) {
                throw new InvalidArgumentException("Channel key '$channelKey' is already defined.");
            }
            $carry[$channelKey] = $channel;
            return $carry;
        }, []), ChannelInterface::class);
    }
}
