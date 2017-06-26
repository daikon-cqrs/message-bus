<?php

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

interface SubscriptionInterface
{
    const METADATA_KEY = "_subscription";

    /**
     * @param EnvelopeInterface $envelope
     * @param MessageBusInterface $messageBus
     * @return bool
     */
    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool;

    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    public function receive(EnvelopeInterface $envelope): bool;

    /**
     * @return string
     */
    public function getKey(): string;
}
