<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use Daikon\MessageBus\Metadata\Metadata;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

final class Envelope implements EnvelopeInterface
{
    private $uuid;

    private $timestamp;

    private $message;

    private $metadata;

    public static function wrap(MessageInterface $message, Metadata $metadata = null): EnvelopeInterface
    {
        return new self($message, $metadata);
    }

    private function __construct(
        MessageInterface $message,
        Metadata $metadata = null,
        Uuid $uuid = null,
        DateTimeImmutable $timestamp = null
    ) {
        $this->message = $message;
        $this->metadata = $metadata ?? Metadata::makeEmpty();
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->timestamp = $timestamp ?? new DateTimeImmutable;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    public function withMetadata(Metadata $metadata): EnvelopeInterface
    {
        $copy = clone $this;
        $copy->metadata = $metadata;
        return $copy;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function toArray(): array
    {
        return [
            "uuid" => $this->uuid->toString(),
            "timestamp" => $this->timestamp->format(self::TIMESTAMP_FORMAT),
            "metadata" => $this->metadata->toArray(),
            "message" => $this->message->toArray(),
            "@message_type" => get_class($this->message)
        ];
    }

    public static function fromArray(array $nativeRepresentation): EnvelopeInterface
    {
        $messageType = $nativeRepresentation['@message_type'];
        return new self(
            $messageType::fromArray($nativeRepresentation['message']),
            Metadata::fromArray($nativeRepresentation["metadata"]),
            Uuid::fromString($nativeRepresentation["uuid"]),
            DateTimeImmutable::createFromFormat(self::TIMESTAMP_FORMAT, $nativeRepresentation["timestamp"])
        );
    }
}
