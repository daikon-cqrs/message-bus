<?php

namespace Accordia\MessageBus\Channel\Subscription\Transport;

use Accordia\MessageBus\EnvelopeInterface;
use Accordia\MessageBus\MessageBusInterface;

interface TransportInterface
{
    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @param EnvelopeInterface $envelope
     * @param MessageBusInterface $messageBus
     * @return bool
     */
    public function send(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool;
}
