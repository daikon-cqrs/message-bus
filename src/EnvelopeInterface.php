<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus;

use Daikon\Interop\FromNativeInterface;
use Daikon\Interop\ToNativeInterface;
use Daikon\Metadata\MetadataInterface;
use DateTimeImmutable;
use Ramsey\Uuid\UuidInterface;

interface EnvelopeInterface extends FromNativeInterface, ToNativeInterface
{
    public const TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s.uP';

    public static function wrap(MessageInterface $message, MetadataInterface $metadata = null): self;

    public function getTimestamp(): DateTimeImmutable;

    public function getUuid(): UuidInterface;

    public function getMetadata(): MetadataInterface;

    public function withMetadata(MetadataInterface $metadata): self;

    public function getMessage(): MessageInterface;
}
