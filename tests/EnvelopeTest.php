<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus;

use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\MessageInterface;
use Daikon\Metadata\Metadata;
use Daikon\Tests\MessageBus\Fixture\ChangeUsername;
use Daikon\Tests\MessageBus\Fixture\KnownRevision;
use Daikon\Tests\MessageBus\Fixture\UserId;
use Daikon\Tests\MessageBus\Fixture\Username;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class EnvelopeTest extends TestCase
{
    public function testWrap(): void
    {
        $envelope = Envelope::wrap(new ChangeUsername(
            new UserId('user-23'),
            new KnownRevision(2),
            new Username('frodo')
        ));
        $this->assertInstanceOf(Uuid::class, $envelope->getUuid());
        $this->assertInstanceOf(DateTimeImmutable::class, $envelope->getTimestamp());
        $this->assertInstanceOf(Metadata::class, $envelope->getMetadata());
        $this->assertInstanceOf(MessageInterface::class, $envelope->getMessage());
    }

    public function testToNativeRoundtrip(): void
    {
        $envelope = Envelope::wrap(new ChangeUsername(
            new UserId('user-23'),
            new KnownRevision(2),
            new Username('frodo')
        ));
        $newEnvelope = Envelope::fromNative($envelope->toNative());
        $this->assertEquals($envelope->toNative(), $newEnvelope->toNative());
    }
}
