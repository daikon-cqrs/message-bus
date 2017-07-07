<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\DataStructure\TypedMapTrait;

final class SubscriptionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    public function __construct(array $subscriptions = [])
    {
        $this->init(array_reduce($subscriptions, function (array $carry, SubscriptionInterface $subscription) {
            $carry[$subscription->getKey()] = $subscription; // enforce consistent channel keys
            return $carry;
        }, []), SubscriptionInterface::CLASS);
    }
}
