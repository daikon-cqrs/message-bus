<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription\MessageHandler;

use Countable;
use Daikon\DataStructure\TypedListTrait;
use IteratorAggregate;

final class MessageHandlerList implements IteratorAggregate, Countable
{
    use TypedListTrait;

    /** @param MessageHandlerInterface[]|self $messageHandlers */
    public function __construct(iterable $messageHandlers = [])
    {
        $this->init($messageHandlers, MessageHandlerInterface::class);
    }
}
