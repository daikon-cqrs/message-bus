<?php

namespace Accordia\MessageBus\Channel\Subscription\Transport;

use Accordia\MessageBus\EnvelopeInterface;
use Accordia\MessageBus\MessageBusInterface;

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
