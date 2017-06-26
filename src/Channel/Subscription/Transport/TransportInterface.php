<?php

namespace Daikon\MessageBus\Channel\Subscription\Transport;

use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

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
