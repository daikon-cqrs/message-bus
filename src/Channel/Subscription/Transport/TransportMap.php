<?php

namespace Daikon\MessageBus\Channel\Subscription\Transport;

use Daikon\DataStructures\TypedMapTrait;

final class TransportMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    /**
     * @param TransportInterface[] $transports
     */
    public function __construct(array $transports = [])
    {
        $this->init(array_reduce($transports, function (array $carry, TransportInterface $transport) {
            $carry[$transport->getKey()] = $transport; // enforce consistent channel keys
            return $carry;
        }, []), TransportInterface::CLASS);
    }
}
