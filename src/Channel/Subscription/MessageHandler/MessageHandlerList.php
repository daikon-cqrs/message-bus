<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel\Subscription\MessageHandler;

use Daikon\DataStructure\TypedListTrait;

final class MessageHandlerList implements \IteratorAggregate, \Countable
{
    use TypedListTrait;

    public function __construct(array $messageHandlers = [])
    {
        $this->init($messageHandlers, MessageHandlerInterface::CLASS);
    }
}
