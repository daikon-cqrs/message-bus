<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus\Channel;

use Daikon\MessageBus\Channel\Channel;
use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Exception\EnvelopeNotAcceptable;
use Daikon\MessageBus\Exception\SubscriptionUnknown;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageInterface;
use Daikon\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class ChannelTest extends TestCase
{
    const CHANNEL_NAME = 'test_channel';

    const SUB_NAME = 'test_subscription';

    public function testGetKey(): void
    {
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap);
        $this->assertEquals($channel->getKey(), self::CHANNEL_NAME);
    }

    public function testPublish(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        $envelopeExpectation = $this->callback(function (EnvelopeInterface $envelope) {
            return self::CHANNEL_NAME === $envelope->getMetadata()->get(ChannelInterface::METADATA_KEY);
        });
        /** @var MessageBusInterface $messageBusMock */
        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $subscriptionMock->expects($this->once())
            ->method('publish')
            ->with($envelopeExpectation, $this->equalTo($messageBusMock));
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap(['mock' => $subscriptionMock]));
        $this->assertNull($channel->publish($envelope, $messageBusMock));
    }

    public function testPublishPreventedByGuard(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        /** @var MessageBusInterface $messageBusMock */
        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $subscriptionMock->expects($this->never())->method('publish');
        $guard = function (EnvelopeInterface $e): bool {
            return $e->getUuid()->toString() === 'this envelope is acceptable';
        };
        $channel = new Channel('foo', new SubscriptionMap(['mock' => $subscriptionMock]), $guard);
        $this->assertNull($channel->publish($envelope, $messageBusMock));
    }

    public function testPublishAcceptedByGuard(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        /** @var MessageBusInterface $messageBusMock */
        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $subscriptionMock->expects($this->once())->method('publish');
        $guard = function (EnvelopeInterface $e): bool {
            return $e->getUuid()->toString() !== 'this envelope is inacceptable';
        };
        $channel = new Channel('foo', new SubscriptionMap(['mock' => $subscriptionMock]), $guard);
        $this->assertNull($channel->publish($envelope, $messageBusMock));
    }

    public function testReceive(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelopeExpectation = Envelope::wrap($messageMock, Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => self::SUB_NAME
        ]));
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $subscriptionMock->expects($this->once())->method('receive')->with($envelopeExpectation);
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap([self::SUB_NAME => $subscriptionMock]));
        $this->assertNull($channel->receive($envelopeExpectation));
    }

    public function testReceiveWithExistingSubscription(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock, Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => 'foobar'
        ]));
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap(['mock' => $subscriptionMock]));

        $this->expectException(SubscriptionUnknown::class);
        $this->expectExceptionMessage(
            "Channel '".self::CHANNEL_NAME."' has no subscription 'foobar' and thus ".
            "Envelope '{$envelope->getUuid()->toString()}' cannot be handled."
        );
        $this->expectExceptionCode(0);

        $channel->receive($envelope);
    }

    public function testReceiveWithMissingChannel(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $envelope = Envelope::wrap($messageMock);
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap(['mock' => $subscriptionMock]));

        $this->expectException(EnvelopeNotAcceptable::class);
        $this->expectExceptionMessage(
            "Channel key '".ChannelInterface::METADATA_KEY."' missing in metadata of Envelope '".
            "{$envelope->getUuid()->toString()}' received on channel '".self::CHANNEL_NAME."'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::CHANNEL_KEY_MISSING);

        $channel->receive($envelope);
    }

    public function testReceiveWithWrongChannel(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $envelope = Envelope::wrap($messageMock, Metadata::fromNative([
            ChannelInterface::METADATA_KEY => 'foobar'
        ]));
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap(['mock' => $subscriptionMock]));

        $this->expectException(EnvelopeNotAcceptable::class);
        $this->expectExceptionMessage(
            "Channel '".self::CHANNEL_NAME."' inadvertently received ".
            "Envelope '{$envelope->getUuid()->toString()}' for channel 'foobar'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::CHANNEL_KEY_UNEXPECTED);

        $channel->receive($envelope);
    }

    public function testReceiveWithMissingSubscription(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $envelope = Envelope::wrap($messageMock, Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME
        ]));
        $channel = new Channel(self::CHANNEL_NAME, new SubscriptionMap(['mock' => $subscriptionMock]));

        $this->expectException(EnvelopeNotAcceptable::class);
        $this->expectExceptionMessage(
            "Subscription key '".SubscriptionInterface::METADATA_KEY."' missing in metadata of ".
            "Envelope '{$envelope->getUuid()->toString()}' received on channel '".self::CHANNEL_NAME."'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING);

        $channel->receive($envelope);
    }
}
