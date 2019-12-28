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

    public function __construct(iterable $transports = [])
    {
        $mappedTransports = [];
        /** @var TransportInterface $transport */
        foreach ($transports as $transport) {
            $transportKey = $transport->getKey();
            if (isset($mappedTransports[$transportKey])) {
                throw new InvalidArgumentException("Transport key '$transportKey' is already defined.");
            }
            $mappedTransports[$transportKey] = $transport;
        }

        $this->init($mappedTransports, TransportInterface::class);
    }
}
