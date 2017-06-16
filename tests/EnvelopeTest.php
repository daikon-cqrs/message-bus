<?php

namespace Accordia\Tests\MessageBus;

use DateTimeImmutable;
use Accordia\MessageBus\Envelope;
use Accordia\MessageBus\MessageInterface;
use Accordia\MessageBus\Metadata\Metadata;
use Accordia\Tests\MessageBus\Fixture\ChangeUsername;
use Accordia\Tests\MessageBus\Fixture\KnownRevision;
use Accordia\Tests\MessageBus\Fixture\Message;
use Accordia\Tests\MessageBus\Fixture\UserId;
use Accordia\Tests\MessageBus\Fixture\Username;
use Ramsey\Uuid\Uuid;
use PHPUnit\Framework\TestCase;

final class EnvelopeTest extends TestCase
{
    public function testWrap()
    {
        $envelope = Envelope::wrap(new ChangeUsername(
            new UserId('user-23'),
            new KnownRevision(2),
            new Username('frodo')
        ));
        $this->assertInstanceOf(Uuid::CLASS, $envelope->getUuid());
        $this->assertInstanceOf(DateTimeImmutable::CLASS, $envelope->getTimestamp());
        $this->assertInstanceOf(Metadata::CLASS, $envelope->getMetadata());
        $this->assertInstanceOf(MessageInterface::CLASS, $envelope->getMessage());
    }

    public function testToNativeRoundtrip()
    {
        $envelope = Envelope::wrap(new ChangeUsername(
            new UserId('user-23'),
            new KnownRevision(2),
            new Username('frodo')
        ));
        $newEnvelope = Envelope::fromArray($envelope->toArray());
        $this->assertEquals($envelope->toArray(), $newEnvelope->toArray());
    }
}
