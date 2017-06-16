<?php

namespace Accordia\MessageBus\Channel\Subscription;

use Accordia\MessageBus\EnvelopeInterface;
use Accordia\MessageBus\MessageBusInterface;

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
