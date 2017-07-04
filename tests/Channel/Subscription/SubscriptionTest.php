<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\MessageBus\Channel\Subscription;

use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\Subscription;
use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportInterface;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageInterface;
use Daikon\MessageBus\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class SubscriptionTest extends TestCase
{
    const CHANNEL_NAME = "test_channel";

    const SUB_NAME = "test_subscription";

    public function testGetKey()
    {
        $subscription = new Subscription(
            self::SUB_NAME,
            $this->createMock(TransportInterface::CLASS),
            new MessageHandlerList
        );
        $this->assertEquals($subscription->getKey(), self::SUB_NAME);
    }

    public function testPublish()
    {
        $envelopeExpectation = $this->callback(function (EnvelopeInterface $envelope) {
            return self::SUB_NAME === $envelope->getMetadata()->get(SubscriptionInterface::METADATA_KEY);
        });
        $messageBusMock = $this->createMock(MessageBusInterface::CLASS);
        $transportMock = $this->getMockBuilder(TransportInterface::CLASS)
            ->setMethods([ "send", "getKey" ])
            ->getMock();
        $transportMock->expects($this->once())
            ->method("send")
            ->with($envelopeExpectation, $this->equalTo($messageBusMock))
            ->willReturn(true);
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList);
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $this->assertTrue($subscription->publish($envelope, $messageBusMock));
    }

    public function testReceive()
    {
        $envelopeExpectation = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::fromArray([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => self::SUB_NAME
        ]));
        $transportMock = $this->createMock(TransportInterface::CLASS);
        $messageHandlerMock = $this->getMockBuilder(MessageHandlerInterface::CLASS)
            ->setMethods([ "handle" ])
            ->getMock();
        $messageHandlerMock->expects($this->once())
            ->method("handle")
            ->with($envelopeExpectation)
            ->willReturn(true);
        $mockedHandlers = new MessageHandlerList([ $messageHandlerMock ]);
        $subscription = new Subscription(self::SUB_NAME, $transportMock, $mockedHandlers);
        $this->assertTrue($subscription->receive($envelopeExpectation));
    }

    public function testReceiveWithWrongSubscription()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::fromArray([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => "foobar"
        ]));
        $subscription = new Subscription(
            self::SUB_NAME,
            $this->createMock(TransportInterface::CLASS),
            new MessageHandlerList
        );

        $this->expectException(EnvelopeNotAcceptable::CLASS);
        $this->expectExceptionMessage(
            "Subscription '".self::SUB_NAME."' inadvertently received Envelope ".
            "'{$envelope->getUuid()}' for subscription 'foobar'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::SUBSCRIPTION_KEY_UNEXPECTED);

        $subscription->receive($envelope);
    } // @codeCoverageIgnore

    public function testReceiveWithMissingSubscription()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $subscription = new Subscription(
            self::SUB_NAME,
            $this->createMock(TransportInterface::CLASS),
            new MessageHandlerList
        );

        $this->expectException(EnvelopeNotAcceptable::CLASS);
        $this->expectExceptionMessage(
            "Subscription key '".SubscriptionInterface::METADATA_KEY."' missing in metadata of ".
            "Envelope '{$envelope->getUuid()}' received by subscription '".self::SUB_NAME."'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING);

        $subscription->receive($envelope);
    } // @codeCoverageIgnore

    public function testPublishPreventedByGuard()
    {
        $messageBusMock = $this->createMock(MessageBusInterface::CLASS);
        $transportMock = $this->getMockBuilder(TransportInterface::CLASS)->getMock();
        $transportMock->expects($this->never())->method("send");
        $accept_nothing_guard = function (EnvelopeInterface $e) {
            return $e->getUuid() === 'this envelope is acceptable';
        };
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList, $accept_nothing_guard);
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $this->assertFalse($subscription->publish($envelope, $messageBusMock));
    }

    public function testPublishAcceptedByGuard()
    {
        $messageBusMock = $this->createMock(MessageBusInterface::CLASS);
        $transportMock = $this->getMockBuilder(TransportInterface::CLASS)->getMock();
        $transportMock->expects($this->once())->method("send")->willReturn(true);
        $accept_all_guard = function (EnvelopeInterface $e) {
            return $e->getUuid() !== 'this envelope is acceptable';
        };
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList, $accept_all_guard);
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $this->assertTrue($subscription->publish($envelope, $messageBusMock));
    }
}
