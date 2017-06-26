<?php

namespace Daikon\MessageBus\Channel;

use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

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
