<?php

namespace Accordia\Tests\MessageBus\Channel\Subscription;

use Accordia\MessageBus\Channel\ChannelInterface;
use Accordia\MessageBus\Channel\Subscription\SubscriptionInterface;
use Accordia\MessageBus\Channel\Subscription\Transport\InProcessTransport;
use Accordia\MessageBus\Envelope;
use Accordia\MessageBus\MessageBusInterface;
use Accordia\MessageBus\MessageInterface;
use Accordia\MessageBus\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class InProcessTransportTest extends TestCase
{
    public function testGetKey()
    {
        $this->assertEquals((new InProcessTransport("inproc"))->getKey(), "inproc");
    }

    public function testSend()
    {
        $envelopeExpectation = Envelope::wrap($this->createMock(MessageInterface::CLASS), Metadata::makeEmpty());
        $transport = new InProcessTransport("inproc");
        $messageBusMock = $this->getMockBuilder(MessageBusInterface::CLASS)
            ->setMethods([ "publish", "receive" ])
            ->getMock();
        $messageBusMock->expects($this->once())
            ->method("receive")
            ->with($envelopeExpectation)
            ->willReturn(true);
        $this->assertTrue($transport->send($envelopeExpectation, $messageBusMock));
    }
}
