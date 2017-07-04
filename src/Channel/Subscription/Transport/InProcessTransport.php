<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel\Subscription\Transport;

use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

final class InProcessTransport implements TransportInterface
{
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function send(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool
    {
        return $messageBus->receive($envelope);
    }

    public function getKey(): string
    {
        return $this->key;
    }
}
