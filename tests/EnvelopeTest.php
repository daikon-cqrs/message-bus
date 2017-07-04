<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\MessageBus;

use DateTimeImmutable;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\MessageInterface;
use Daikon\MessageBus\Metadata\Metadata;
use Daikon\Tests\MessageBus\Fixture\ChangeUsername;
use Daikon\Tests\MessageBus\Fixture\KnownRevision;
use Daikon\Tests\MessageBus\Fixture\Message;
use Daikon\Tests\MessageBus\Fixture\UserId;
use Daikon\Tests\MessageBus\Fixture\Username;
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
