<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus\Channel\Subscription;

use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerInterface;
use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\Subscription;
use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportInterface;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Exception\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageInterface;
use Daikon\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class SubscriptionTest extends TestCase
{
    const CHANNEL_NAME = 'test_channel';

    const SUB_NAME = 'test_subscription';

    public function testGetKey(): void
    {
        /** @var TransportInterface $transportMock */
        $transportMock = $this->createMock(TransportInterface::class);
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList);
        $this->assertEquals($subscription->getKey(), self::SUB_NAME);
    }

    public function testPublish(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        $envelopeExpectation = $this->callback(function (EnvelopeInterface $envelope) {
            return self::SUB_NAME === $envelope->getMetadata()->get(SubscriptionInterface::METADATA_KEY);
        });
        /** @var MessageBusInterface $messageBusMock */
        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $transportMock = $this->createMock(TransportInterface::class);
        $transportMock->expects($this->once())
            ->method('send')
            ->with($envelopeExpectation, $this->equalTo($messageBusMock));
        /** @var TransportInterface $transportMock */
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList);
        $this->assertNull($subscription->publish($envelope, $messageBusMock));
    }

    public function testReceive(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelopeExpectation = Envelope::wrap($messageMock, Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => self::SUB_NAME
        ]));
        /** @var TransportInterface $transportMock */
        $transportMock = $this->createMock(TransportInterface::class);
        $messageHandlerMock = $this->createMock(MessageHandlerInterface::class);
        $messageHandlerMock->expects($this->once())->method('handle')->with($envelopeExpectation);
        /** @var MessageHandlerInterface $messageHandlerMock */
        $mockedHandlers = new MessageHandlerList([$messageHandlerMock]);
        $subscription = new Subscription(self::SUB_NAME, $transportMock, $mockedHandlers);
        $this->assertNull($subscription->receive($envelopeExpectation));
    }

    public function testReceiveWithWrongSubscription(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock, Metadata::fromNative([
            ChannelInterface::METADATA_KEY => self::CHANNEL_NAME,
            SubscriptionInterface::METADATA_KEY => 'foobar'
        ]));
        /** @var TransportInterface $transportMock */
        $transportMock = $this->createMock(TransportInterface::class);
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList);

        $this->expectException(EnvelopeNotAcceptable::class);
        $this->expectExceptionMessage(
            "Subscription '".self::SUB_NAME."' inadvertently received Envelope ".
            "'{$envelope->getUuid()->toString()}' for subscription 'foobar'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::SUBSCRIPTION_KEY_UNEXPECTED);

        $subscription->receive($envelope);
    }

    public function testReceiveWithMissingSubscription(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        /** @var TransportInterface $transportMock */
        $transportMock = $this->createMock(TransportInterface::class);
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList);

        $this->expectException(EnvelopeNotAcceptable::class);
        $this->expectExceptionMessage(
            "Subscription key '".SubscriptionInterface::METADATA_KEY."' missing in metadata of ".
            "Envelope '{$envelope->getUuid()->toString()}' received by subscription '".self::SUB_NAME."'."
        );
        $this->expectExceptionCode(EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING);

        $subscription->receive($envelope);
    }

    public function testPublishPreventedByGuard(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        /** @var MessageBusInterface $messageBusMock */
        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $transportMock = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transportMock->expects($this->never())->method('send');
        $guardNone = function (EnvelopeInterface $e): bool {
            return $e->getUuid()->toString() === 'this envelope is acceptable';
        };
        /** @var TransportInterface $transportMock */
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList, $guardNone);
        $this->assertNull($subscription->publish($envelope, $messageBusMock));
    }

    public function testPublishAcceptedByGuard(): void
    {
        /** @var MessageBusInterface $messageBusMock */
        $messageBusMock = $this->createMock(MessageBusInterface::class);
        $transportMock = $this->getMockBuilder(TransportInterface::class)->getMock();
        $transportMock->expects($this->once())->method('send');
        $guardAll = function (EnvelopeInterface $e): bool {
            return $e->getUuid()->toString() !== 'this envelope is acceptable';
        };
        /** @var TransportInterface $transportMock */
        $subscription = new Subscription(self::SUB_NAME, $transportMock, new MessageHandlerList, $guardAll);
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelope = Envelope::wrap($messageMock);
        $this->assertNull($subscription->publish($envelope, $messageBusMock));
    }
}
