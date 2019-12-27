<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription\Transport;

use Countable;
use Daikon\DataStructure\TypedMapTrait;
use InvalidArgumentException;
use IteratorAggregate;

final class TransportMap implements IteratorAggregate, Countable
{
    use TypedMapTrait;

    /** @param TransportInterface[] $transports */
    public function __construct(array $transports = [])
    {
        $this->init(array_reduce($transports, function (array $carry, TransportInterface $transport): array {
            $transportKey = $transport->getKey();
            if (isset($carry[$transportKey])) {
                throw new InvalidArgumentException("Transport key '$transportKey' is already defined.");
            }
            $carry[$transportKey] = $transport;
            return $carry;
        }, []), TransportInterface::class);
    }
}
