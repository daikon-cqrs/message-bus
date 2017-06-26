<?php

namespace Daikon\MessageBus;

use DateTimeImmutable;
use Daikon\MessageBus\Metadata\Metadata;
use Ramsey\Uuid\Uuid;

interface EnvelopeInterface
{
    const TIMESTAMP_FORMAT = "Y-m-d\\TH:i:s.uP";

    /**
     * @param MessageInterface $message
     * @param Metadata|null $metadata
     * @return EnvelopeInterface
     */
    public static function wrap(MessageInterface $message, Metadata $metadata = null): EnvelopeInterface;

    /**
     * @return DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable;

    /**
     * @return Uuid
     */
    public function getUuid(): Uuid;

    /**
     * @return Metadata
     */
    public function getMetadata(): Metadata;

    /**
     * @param Metadata $metadata
     * @return EnvelopeInterface
     */
    public function withMetadata(Metadata $metadata): EnvelopeInterface;

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface;

    /**
     * @return mixed[]
     */
    public function toArray(): array;

    /**
     * @param array $nativeRepresentation
     * @return EnvelopeInterface
     */
    public static function fromArray(array $nativeRepresentation): EnvelopeInterface;
}
