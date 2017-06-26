<?php

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\DataStructures\TypedMapTrait;

final class SubscriptionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    /**
     * @param SubscriptionInterface[] $subscriptions
     */
    public function __construct(array $subscriptions = [])
    {
        $this->init(array_reduce($subscriptions, function (array $carry, SubscriptionInterface $subscription) {
            $carry[$subscription->getKey()] = $subscription; // enforce consistent channel keys
            return $carry;
        }, []), SubscriptionInterface::CLASS);
    }
}
