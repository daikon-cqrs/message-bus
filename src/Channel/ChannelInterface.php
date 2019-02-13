<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel;

use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;

interface ChannelInterface
{
    const METADATA_KEY = "_channel";

    public function getKey(): string;

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): void;

    public function receive(EnvelopeInterface $envelope): void;
}
