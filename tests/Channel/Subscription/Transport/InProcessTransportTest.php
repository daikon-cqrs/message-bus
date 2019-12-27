<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus\Channel\Subscription;

use Daikon\MessageBus\Channel\Subscription\Transport\InProcessTransport;
use Daikon\MessageBus\Envelope;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\MessageInterface;
use Daikon\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class InProcessTransportTest extends TestCase
{
    public function testGetKey(): void
    {
        $this->assertEquals((new InProcessTransport('inproc'))->getKey(), 'inproc');
    }

    public function testSend(): void
    {
        /** @var MessageInterface $messageMock */
        $messageMock = $this->createMock(MessageInterface::class);
        $envelopeExpectation = Envelope::wrap($messageMock, Metadata::makeEmpty());
        $transport = new InProcessTransport('inproc');
        $messageBusMock = $this->getMockBuilder(MessageBusInterface::class)
            ->setMethods(['publish', 'receive'])
            ->getMock();
        $messageBusMock->expects($this->once())
            ->method('receive')
            ->with($envelopeExpectation);
        /** @psalm-suppress InvalidArgument */
        $this->assertNull($transport->send($envelopeExpectation, $messageBusMock));
    }
}
