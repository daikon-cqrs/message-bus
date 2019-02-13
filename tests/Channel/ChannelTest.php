<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\MessageBus\Channel;

use Daikon\MessageBus\Channel\Channel;
use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\Error\SubscriptionUnknown;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageInterface;
use Daikon\MessageBus\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class ChannelTest extends TestCase
{
    const CHANNEL_NAME = "test_channel";

    const SUB_NAME = "test_subscription";

    public function testGetKey()
    {
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap);
        $this->assertEquals($channel->getKey(), self::CHANNEL_NAME);
    }

    public function testPublish()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $envelopeExpectation = $this->callback(function (EnvelopeInterface $envelope) {
            return self::CHANNEL_NAME === $envelope->getMetadata()->get(ChannelInterface::METADATA_KEY);
        });
        $messageBusMock = $this->createMock(MessageBusInterface::CLASS);
        $subscriptionMock = $this->getMockBuilder(SubscriptionInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $subscriptionMock->expects($this->once())
            ->method("publish")
            ->with($envelopeExpectation, $this->equalTo($messageBusMock));
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap([ $subscriptionMock ]));
        $this->assertNull($channel->publish($envelope, $messageBusMock));
    }

    public function testPublishPreventedByGuard()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $messageBusMock = $this->createMock(MessageBusInterface::CLASS);
        $subscriptionMock = $this->getMockBuilder(SubscriptionInterface::CLASS)->getMock();
        $subscriptionMock->expects($this->never())->method("publish");
        $guard = function (EnvelopeInterface $e) {
            return $e->getUuid() === 'this envelope is acceptable';
        };
        $channel = new Channel('foo', new SubscriptionMap([$subscriptionMock]), $guard);
        $this->assertNull($channel->publish($envelope, $messageBusMock));
    }

    public function testPublishAcceptedByGuard()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $messageBusMock = $this->createMock(MessageBusInterface::CLASS);
        $subscriptionMock = $this->getMockBuilder(SubscriptionInterface::CLASS)->getMock();
        $subscriptionMock->expects($this->once())->method("publish");
        $guard = function (EnvelopeInterface $e) {
            return $e->getUuid() !== 'this envelope is inacceptable';
        };
        $channel = new Channel('foo', new SubscriptionMap([$subscriptionMock]), $guard);
        $this->assertNull($channel->publish($envelope, $messageBusMock));
    }

    public function testReceive()
    {
        $envelopeExpectation = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => self::SUB_NAME
        ]));
        $subscriptionMock = $this->getMockBuilder(SubscriptionInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $subscriptionMock->expects($this->once())
            ->method("getKey")
            ->willReturn(self::SUB_NAME);
        $subscriptionMock->expects($this->once())
            ->method("receive")
            ->with($envelopeExpectation);
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap([ $subscriptionMock ]));
        $this->assertNull($channel->receive($envelopeExpectation));
    }

    public function testReceiveWithExistingSubscription()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => "foobar"
        ]));
        $subscriptionMock = $this->getMockBuilder(SubscriptionInterface::CLASS)
            ->setMethods([ "publish", "receive", "getKey" ])
            ->getMock();
        $subscriptionMock->expects($this->once())
            ->method("getKey")
            ->willReturn(self::SUB_NAME);
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap([ $subscriptionMock ]));

        $this->expectException(SubscriptionUnknown::CLASS);
        $this->expectExceptionMessage(
            "Channel '".self::CHANNEL_NAME."' has no subscription 'foobar' and thus ".
            "Envelope '{$envelope->getUuid()}' cannot be handled."
        );
        $this->expectExceptionCode(0);

        $channel->receive($envelope);
    } // @codeCoverageIgnore

    public function testReceiveWithMissingChannel()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS));
        $channel = new Channel(
            self::CHANNEL_NAME,
            new SubscriptionMap([ $this->createMock(SubscriptionInterface::CLASS) ])
        );

        $this->expectException(EnvelopeNotAcceptable::CLASS);
        $this->expectExceptionMessage(
            "Channel key '".ChannelInterface::METADATA_KEY."' missing in metadata of Envelope '".
            "{$envelope->getUuid()}' received on channel '".self::CHANNEL_NAME."'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::CHANNEL_KEY_MISSING);

        $channel->receive($envelope);
    } // @codeCoverageIgnore

    public function testReceiveWithWrongChannel()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::fromNative([
            ChannelInterface::METADATA_KEY => "foobar"
        ]));
        $channel = new Channel(
            self::CHANNEL_NAME,
            new SubscriptionMap([ $this->createMock(SubscriptionInterface::CLASS) ])
        );

        $this->expectException(EnvelopeNotAcceptable::CLASS);
        $this->expectExceptionMessage(
            "Channel '".self::CHANNEL_NAME."' inadvertently received ".
            "Envelope '{$envelope->getUuid()}' for channel 'foobar'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::CHANNEL_KEY_UNEXPECTED);

        $channel->receive($envelope);
    } // @codeCoverageIgnore

    public function testReceiveWithMissingSubscription()
    {
        $envelope = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME
        ]));
        $channel = new Channel(
            self::CHANNEL_NAME,
            new SubscriptionMap([ $this->createMock(SubscriptionInterface::CLASS) ])
        );

        $this->expectException(EnvelopeNotAcceptable::CLASS);
        $this->expectExceptionMessage(
            "Subscription key '".SubscriptionInterface::METADATA_KEY."' missing in metadata of ".
            "Envelope '{$envelope->getUuid()}' received on channel '".self::CHANNEL_NAME."'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING);

        $channel->receive($envelope);
    } // @codeCoverageIgnore
}
