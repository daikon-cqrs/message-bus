<?php

namespace Accordia\MessageBus\Channel;

use Accordia\MessageBus\EnvelopeInterface;
use Accordia\MessageBus\MessageBusInterface;

interface ChannelInterface
{
    const METADATA_KEY = "_channel";

    /**
     * @return string
     */
    public function getKey(): string;

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
}
