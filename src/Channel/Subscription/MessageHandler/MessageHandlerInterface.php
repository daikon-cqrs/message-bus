<?php

namespace Accordia\MessageBus\Channel\Subscription\MessageHandler;

use Accordia\MessageBus\EnvelopeInterface;

interface MessageHandlerInterface
{
    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    public function handle(EnvelopeInterface $envelope): bool;
}
