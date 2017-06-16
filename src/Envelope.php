<?php

namespace Accordia\MessageBus;

use DateTimeImmutable;
use Accordia\MessageBus\Metadata\Metadata;
use Ramsey\Uuid\Uuid;

final class Envelope implements EnvelopeInterface
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var DateTimeImmutable
     */
    private $timestamp;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var Metadata
     */
    private $metadata;

    /**
     * @param MessageInterface $message
     * @param Metadata|null $metadata
     * @return EnvelopeInterface
     */
    public static function wrap(MessageInterface $message, Metadata $metadata = null): EnvelopeInterface
    {
        return new self($message, $metadata);
    }

    /**
     * @param MessageInterface $message
     * @param Metadata|null $metadata
     * @param Uuid|null $uuid
     * @param DateTimeImmutable|null $timestamp
     */
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

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @return Uuid
     */
    public function getUuid(): Uuid
    {
        return $this->uuid;
    }

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    /**
     * @param Metadata $metadata
     * @return EnvelopeInterface
     */
    public function withMetadata(Metadata $metadata): EnvelopeInterface
    {
        $copy = clone $this;
        $copy->metadata = $metadata;
        return $copy;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
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

    /**
     * @param array $nativeRepresentation
     * @return EnvelopeInterface
     */
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
