<?php

namespace Daikon\MessageBus\Channel\Subscription\MessageHandler;

use Daikon\MessageBus\EnvelopeInterface;

interface MessageHandlerInterface
{
    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    public function handle(EnvelopeInterface $envelope): bool;
}
