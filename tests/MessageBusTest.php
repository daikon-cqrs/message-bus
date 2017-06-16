<?php

namespace Accordia\Tests\MessageBus;

use Accordia\MessageBus\Channel\ChannelInterface;
use Accordia\MessageBus\Channel\ChannelMap;
use Accordia\MessageBus\Envelope;
use Accordia\MessageBus\EnvelopeInterface;
use Accordia\MessageBus\Error\ChannelUnknown;
use Accordia\MessageBus\Error\EnvelopeNotAcceptable;
use Accordia\MessageBus\MessageBus;
use Accordia\MessageBus\MessageInterface;
use Accordia\MessageBus\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class MessageBusTest extends TestCase
{
    const CHANNEL_NAME = "commands";

    public function testPublish()
    {
        $messageMock = $this->createMock(MessageInterface::CLASS);
        $envelopeExpectation = $this->callback(function (EnvelopeInterface $envelope) use ($messageMock) {
            return $messageMock === $envelope->getMessage();
        });
        $channelMock = $this->getMockBuilder(ChannelInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $channelMock->expects($this->once())
            ->method("getKey")
            ->willReturn(self::CHANNEL_NAME);
        $channelMock->expects($this->once())
            ->method("publish")
            ->with($envelopeExpectation)
            ->willReturn(true);
        $messageBus = new MessageBus(new ChannelMap([ $channelMock ]));
        $this->assertTrue($messageBus->publish($messageMock, self::CHANNEL_NAME));
    }

    public function testReceive()
    {
        $envelopeExpectation = Envelope::wrap(
            $this->createMock(MessageInterface::CLASS),
            Metadata::makeEmpty()->with(ChannelInterface::METADATA_KEY, self::CHANNEL_NAME)
        );
        $channelMock = $this->getMockBuilder(ChannelInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $channelMock->expects($this->once())
            ->method("getKey")
            ->willReturn(self::CHANNEL_NAME);
        $channelMock->expects($this->once())
            ->method("receive")
            ->with($envelopeExpectation)
            ->willReturn(true);
        $messageBus = new MessageBus(new ChannelMap([ $channelMock ]));
        $this->assertTrue($messageBus->receive($envelopeExpectation));
    }

    public function testPublishToNonExistingChannel()
    {
        $channelMock = $this->getMockBuilder(ChannelInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $channelMock->expects($this->once())
            ->method("getKey")
            ->willReturn(self::CHANNEL_NAME);
        $messageBus = new MessageBus(new ChannelMap([ $channelMock ]));

        $this->expectException(ChannelUnknown::CLASS);
        $this->expectExceptionMessage("Channel 'events' has not been registered on message bus.");
        $this->expectExceptionCode(0);

        $messageBus->publish($this->createMock(MessageInterface::CLASS), "events");
    } // @codeCoverageIgnore

    public function testReceiveFromNonExistingChannel()
    {
        $envelope = Envelope::wrap(
            $this->createMock(MessageInterface::CLASS),
            Metadata::makeEmpty()->with(ChannelInterface::METADATA_KEY, "events")
        );
        $channelMock = $this->getMockBuilder(ChannelInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $channelMock->expects($this->once())
            ->method("getKey")
            ->willReturn(self::CHANNEL_NAME);
        $messageBus = new MessageBus(new ChannelMap([ $channelMock ]));

        $this->expectException(ChannelUnknown::CLASS);
        $this->expectExceptionMessage("Channel 'events' has not been registered on message bus.");
        $this->expectExceptionCode(0);

        $messageBus->receive($envelope);
    } // @codeCoverageIgnore

    public function testReceiveEnvelopeWithMissingChannel()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $messageBus = new MessageBus(new ChannelMap([ $this->createMock(ChannelInterface::CLASS) ]));

        $this->expectException(EnvelopeNotAcceptable::CLASS);
        $this->expectExceptionMessage(
            "Channel key '".ChannelInterface::METADATA_KEY."' missing in metadata of ".
            "Envelope '{$envelope->getUuid()}' received on message bus."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::CHANNEL_KEY_MISSING);

        $messageBus->receive($envelope);
    } // @codeCoverageIgnore
}
