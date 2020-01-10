<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel;

use Daikon\DataStructure\TypedMapInterface;
use Daikon\DataStructure\TypedMapTrait;
use InvalidArgumentException;

final class ChannelMap implements TypedMapInterface
{
    use TypedMapTrait;

    public function __construct(iterable $channels = [])
    {
        $mappedChannels = [];
        /** @var ChannelInterface $channel */
        foreach ($channels as $channel) {
            $channelKey = $channel->getKey();
            if (isset($mappedChannels[$channelKey])) {
                throw new InvalidArgumentException("Channel key '$channelKey' is already defined.");
            }
            $mappedChannels[$channelKey] = $channel;
        }

        $this->init($mappedChannels, [ChannelInterface::class]);
    }
}
