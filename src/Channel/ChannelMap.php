<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel;

use Daikon\DataStructure\TypedMapTrait;
use Daikon\MessageBus\Error\ConfigurationError;

final class ChannelMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $channels = [])
    {
        $this->init(array_reduce($channels, function (array $carry, ChannelInterface $channel): array {
            $channelKey = $channel->getKey();
            if (isset($carry[$channelKey])) {
                throw new ConfigurationError("Channel key '$channelKey' is already defined.");
            }
            $carry[$channelKey] = $channel;
            return $carry;
        }, []), ChannelInterface::class);
    }
}
