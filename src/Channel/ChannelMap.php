<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel;

use Daikon\DataStructure\TypedMap;

final class ChannelMap extends TypedMap
{
    public function __construct(iterable $channels = [])
    {
        $this->init($channels, [ChannelInterface::class]);
    }
}
