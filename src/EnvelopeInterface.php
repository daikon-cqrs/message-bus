<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use DateTimeImmutable;
use Daikon\MessageBus\Metadata\Metadata;
use Ramsey\Uuid\Uuid;

interface EnvelopeInterface
{
    const TIMESTAMP_FORMAT = "Y-m-d\\TH:i:s.uP";

    public static function wrap(MessageInterface $message, Metadata $metadata = null): EnvelopeInterface;

    public function getTimestamp(): DateTimeImmutable;

    public function getUuid(): Uuid;

    public function getMetadata(): Metadata;

    public function withMetadata(Metadata $metadata): EnvelopeInterface;

    public function getMessage(): MessageInterface;

    public function toArray(): array;

    public static function fromArray(array $nativeRepresentation): EnvelopeInterface;
}
