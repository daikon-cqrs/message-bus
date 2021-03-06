<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus;

use DateTimeImmutable;
use Daikon\MessageBus\Exception\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageInterface;
use Daikon\Metadata\Metadata;
use Daikon\Metadata\MetadataInterface;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;

final class Envelope implements EnvelopeInterface
{
    private UuidInterface $uuid;

    private DateTimeImmutable $timestamp;

    private MessageInterface $message;

    private MetadataInterface $metadata;

    public static function wrap(MessageInterface $message, MetadataInterface $metadata = null): self
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

    public function withMetadata(MetadataInterface $metadata): self
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
            'uuid' => $this->uuid->toString(),
            'timestamp' => $this->timestamp->format(self::TIMESTAMP_FORMAT),
            'metadata' => $this->metadata->toNative(),
            'message' => $this->message->toNative(),
            '@message_type' => get_class($this->message),
            '@metadata_type' => get_class($this->metadata)
        ];
    }

    /** @param array $state */
    public static function fromNative($state): self
    {
        $uuid = isset($state['uuid']) ? Uuid::fromString($state['uuid']) : null;

        $timestamp = isset($state['timestamp'])
            ? DateTimeImmutable::createFromFormat(self::TIMESTAMP_FORMAT, $state['timestamp'])
            : null;
        if (false === $timestamp) {
            throw new EnvelopeNotAcceptable('Unable to parse given timestamp.', EnvelopeNotAcceptable::UNPARSEABLE);
        }

        $messageType = $state['@message_type'] ?? null;
        if (is_null($messageType) || !is_subclass_of($messageType, MessageInterface::class)) {
            throw new EnvelopeNotAcceptable(
                sprintf("Message type '%s' given must be an instance of MessageInterface.", $messageType ?? 'null'),
                EnvelopeNotAcceptable::UNPARSEABLE
            );
        }

        $metadataType = $state['@metadata_type'] ?? null;
        /** @var MetadataInterface $metadata */
        $metadata = $metadataType instanceof MetadataInterface
            ? $metadataType::fromNative($state['metadata'])
            : Metadata::fromNative($state['metadata'] ?? []);

        /** @var MessageInterface $message */
        $message = $messageType::fromNative($state['message'] ?? null);

        return new self($message, $metadata, $uuid, $timestamp);
    }
}
