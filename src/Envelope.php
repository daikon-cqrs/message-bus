<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use Daikon\MessageBus\Metadata\MetadataInterface;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\Metadata\Metadata;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;

final class Envelope implements EnvelopeInterface
{
    /**
     * @var UuidInterface
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
     * @var MetadataInterface
     */
    private $metadata;

    public static function wrap(MessageInterface $message, MetadataInterface $metadata = null): EnvelopeInterface
    {
        return new self($message, $metadata);
    }

    private function __construct(
        MessageInterface $message,
        MetadataInterface $metadata = null,
        UuidInterface $uuid = null,
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

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getMetadata(): MetadataInterface
    {
        return $this->metadata;
    }

    public function withMetadata(MetadataInterface $metadata): EnvelopeInterface
    {
        $copy = clone $this;
        $copy->metadata = $metadata;
        return $copy;
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }

    public function toNative(): array
    {
        return [
            "uuid" => $this->uuid->toString(),
            "timestamp" => $this->timestamp->format(self::TIMESTAMP_FORMAT),
            "metadata" => $this->metadata->toNative(),
            "message" => $this->message->toNative(),
            "@message_type" => get_class($this->message)
        ];
    }

    /** @param array $state */
    public static function fromNative($state): EnvelopeInterface
    {
        $timestamp = DateTimeImmutable::createFromFormat(self::TIMESTAMP_FORMAT, $state["timestamp"]);
        if (false === $timestamp) {
            throw new EnvelopeNotAcceptable("Unable to parse given timestamp.", EnvelopeNotAcceptable::UNPARSEABLE);
        }
        $messageType = $state['@message_type'];
        // @todo support any MetadataInterface impl and resolve it from @metadata_type
        return new self(
            $messageType::fromNative($state['message']),
            Metadata::fromNative($state["metadata"]),
            Uuid::fromString($state["uuid"]),
            $timestamp
        );
    }
}
