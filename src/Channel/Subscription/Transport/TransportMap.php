<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription\Transport;

use Daikon\DataStructure\TypedMap;

final class TransportMap extends TypedMap
{
    public function __construct(iterable $transports = [])
    {
        $this->init($transports, [TransportInterface::class]);
    }
}
