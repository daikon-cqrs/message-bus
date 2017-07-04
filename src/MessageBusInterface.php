<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use Daikon\MessageBus\Metadata\Metadata;

interface MessageBusInterface
{
    public function publish(MessageInterface $message, string $channel, Metadata $metadata = null): bool;

    public function receive(EnvelopeInterface $envelope): bool;
}
