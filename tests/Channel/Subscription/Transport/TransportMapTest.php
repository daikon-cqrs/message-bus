<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus;

use Daikon\MessageBus\Channel\Subscription\Transport\TransportInterface;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportMap;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TransportMapTest extends TestCase
{
    public function testConstructWithSelf(): void
    {
        /** @var TransportInterface $transportMock */
        $transportMock = $this->createMock(TransportInterface::class);
        $transportMap = new TransportMap([$transportMock]);
        $newMap = new TransportMap($transportMap);
        $this->assertCount(1, $newMap);
        $this->assertFalse($transportMap === $newMap);
    }

    public function testConstructWithDuplicateKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $transportMock = $this->createMock(TransportInterface::class);
        $transportMock->expects($this->exactly(2))->method('getKey')->willReturn('mock');
        new TransportMap([$transportMock, $transportMock]);
    }

    public function testPush(): void
    {
        $emptyMap = new TransportMap;
        /** @var TransportInterface $transportMock */
        $transportMock = $this->createMock(TransportInterface::class);
        $transportMap = $emptyMap->set('mock', $transportMock);
        $this->assertCount(1, $transportMap);
        $this->assertEquals($transportMock, $transportMap->get('mock'));
        $this->assertTrue($emptyMap->isEmpty());
    }
}
