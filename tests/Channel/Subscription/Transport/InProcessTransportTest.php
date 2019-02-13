<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\MessageBus\Channel\Subscription;

use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\Transport\InProcessTransport;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageInterface;
use Daikon\MessageBus\Metadata\Metadata;
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
            ->with($envelopeExpectation);
        $this->assertNull($transport->send($envelopeExpectation, $messageBusMock));
    }
}
