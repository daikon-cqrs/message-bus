<?php

namespace Daikon\MessageBus\Channel\Subscription\Transport;

use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

final class InProcessTransport implements TransportInterface
{
    /**
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @param EnvelopeInterface $envelope
     * @param MessageBusInterface $messageBus
     * @return bool
     */
    public function send(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool
    {
        return $messageBus->receive($envelope);
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
