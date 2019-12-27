<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus;

use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Error\ChannelUnknown;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageBus;
use Daikon\MessageBus\MessageInterface;
use Daikon\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class MessageBusTest extends TestCase
{
    const CHANNEL_NAME = 'commands';

    public function testPublish(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelopeExpectation = $this->callback(function (EnvelopeInterface $envelope) use ($messageMock) {
            return $messageMock === $envelope->getMessage();
        });
        $channelMock = $this->getMockBuilder(ChannelInterface::class)
            ->setMethods(['publish', 'receive', 'getKey'])
            ->getMock();
        $channelMock->expects($this->once())
            ->method('getKey')
            ->willReturn(self::CHANNEL_NAME);
        $channelMock->expects($this->once())
            ->method('publish')
            ->with($envelopeExpectation);
        /** @psalm-suppress InvalidArgument */
        $messageBus = new MessageBus(new ChannelMap([$channelMock]));
        $this->assertNull($messageBus->publish($messageMock, self::CHANNEL_NAME));
    }

    public function testReceive(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelopeExpectation = Envelope::wrap(
            $messageMock,
            Metadata::makeEmpty()->with(ChannelInterface::METADATA_KEY, self::CHANNEL_NAME)
        );
        $channelMock = $this->getMockBuilder(ChannelInterface::class)
            ->setMethods(['publish', 'receive', 'getKey'])
            ->getMock();
        $channelMock->expects($this->once())
            ->method('getKey')
            ->willReturn(self::CHANNEL_NAME);
        $channelMock->expects($this->once())
            ->method('receive')
            ->with($envelopeExpectation);
        /** @psalm-suppress InvalidArgument */
        $messageBus = new MessageBus(new ChannelMap([$channelMock]));
        $this->assertNull($messageBus->receive($envelopeExpectation));
    }

    public function testPublishToNonExistingChannel(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $channelMock = $this->getMockBuilder(ChannelInterface::class)
            ->setMethods(['publish', 'receive', 'getKey'])
            ->getMock();
        $channelMock->expects($this->once())
            ->method('getKey')
            ->willReturn(self::CHANNEL_NAME);
        /** @psalm-suppress InvalidArgument */
        $messageBus = new MessageBus(new ChannelMap([$channelMock]));

        $this->expectException(ChannelUnknown::class);
        $this->expectExceptionMessage("Channel 'events' has not been registered on message bus.");
        $this->expectExceptionCode(0);

        $messageBus->publish($messageMock, 'events');
    } // @codeCoverageIgnore

    public function testReceiveFromNonExistingChannel(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap(
            $messageMock,
            Metadata::makeEmpty()->with(ChannelInterface::METADATA_KEY, 'events')
        );
        $channelMock = $this->getMockBuilder(ChannelInterface::class)
            ->setMethods(['publish', 'receive', 'getKey'])
            ->getMock();
        $channelMock->expects($this->once())
            ->method('getKey')
            ->willReturn(self::CHANNEL_NAME);
        /** @psalm-suppress InvalidArgument */
        $messageBus = new MessageBus(new ChannelMap([$channelMock]));

        $this->expectException(ChannelUnknown::class);
        $this->expectExceptionMessage("Channel 'events' has not been registered on message bus.");
        $this->expectExceptionCode(0);

        $messageBus->receive($envelope);
    } // @codeCoverageIgnore

    public function testReceiveEnvelopeWithMissingChannel(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        /** @var ChannelInterface $channelMock */
        $channelMock = $this->createMock(ChannelInterface::class);
        $messageBus = new MessageBus(new ChannelMap([$channelMock]));

        $this->expectException(EnvelopeNotAcceptable::class);
        $this->expectExceptionMessage(
            "Channel key '".ChannelInterface::METADATA_KEY."' missing in metadata of ".
            "Envelope '{$envelope->getUuid()->toString()}' received on message bus."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::CHANNEL_KEY_MISSING);

        $messageBus->receive($envelope);
    } // @codeCoverageIgnore
}
