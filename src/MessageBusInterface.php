<?php

namespace Accordia\MessageBus;

use Accordia\MessageBus\Metadata\Metadata;

interface MessageBusInterface
{
    /**
     * @param MessageInterface $message
     * @param string $channel
     * @param Metadata|null $metadata
     * @return bool
     */
    public function publish(MessageInterface $message, string $channel, Metadata $metadata = null): bool;

    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    public function receive(EnvelopeInterface $envelope): bool;
}
