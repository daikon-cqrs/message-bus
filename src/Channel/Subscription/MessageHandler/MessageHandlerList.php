<?php

namespace Daikon\MessageBus\Channel\Subscription\MessageHandler;

use Daikon\DataStructures\TypedListTrait;

final class MessageHandlerList implements \IteratorAggregate, \Countable
{
    use TypedListTrait;

    /**
     * @param MessageHandlerInterface[] $messageHandlers
     */
    public function __construct(array $messageHandlers = [])
    {
        $this->init($messageHandlers, MessageHandlerInterface::CLASS);
    }
}
