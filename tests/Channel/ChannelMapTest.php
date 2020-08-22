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
use PHPUnit\Framework\TestCase;

final class ChannelMapTest extends TestCase
{
    public function testConstructWithSelf(): void
    {
        $channelMock = $this->createMock(ChannelInterface::class);
        $channelMap = new ChannelMap(['mock' => $channelMock]);
        $newMap = new ChannelMap($channelMap);
        $this->assertCount(1, $newMap);
        $this->assertFalse($channelMap === $newMap);
    }

    public function testPush(): void
    {
        $emptyMap = new ChannelMap;
        /** @var ChannelInterface $channelMock */
        $channelMock = $this->createMock(ChannelInterface::class);
        $channelMap = $emptyMap->with('mock', $channelMock);
        $this->assertCount(1, $channelMap);
        $this->assertEquals($channelMock, $channelMap->get('mock'));
        $this->assertTrue($emptyMap->isEmpty());
    }
}
