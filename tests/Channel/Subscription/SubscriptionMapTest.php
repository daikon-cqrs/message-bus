<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus;

use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use PHPUnit\Framework\TestCase;

final class SubscriptionMapTest extends TestCase
{
    public function testConstructWithSelf(): void
    {
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $subscriptionMap = new SubscriptionMap(['mock' => $subscriptionMock]);
        $newMap = new SubscriptionMap($subscriptionMap);
        $this->assertCount(1, $newMap);
        $this->assertFalse($subscriptionMap === $newMap);
    }

    public function testPush(): void
    {
        $emptyMap = new SubscriptionMap;
        /** @var SubscriptionInterface $subscriptionMock */
        $subscriptionMock = $this->createMock(SubscriptionInterface::class);
        $subscriptionMap = $emptyMap->with('mock', $subscriptionMock);
        $this->assertCount(1, $subscriptionMap);
        $this->assertEquals($subscriptionMock, $subscriptionMap->get('mock'));
        $this->assertTrue($emptyMap->isEmpty());
    }
}
