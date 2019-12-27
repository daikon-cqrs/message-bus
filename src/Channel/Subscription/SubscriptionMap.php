<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\DataStructure\TypedMapTrait;
use InvalidArgumentException;

final class SubscriptionMap implements \IteratorAggregate, \Countable
{
    use TypedMapTrait;

    /** @param SubscriptionInterface[] $subscriptions */
    public function __construct(array $subscriptions = [])
    {
        $this->init(array_reduce($subscriptions, function (array $carry, SubscriptionInterface $subscription): array {
            $subscriptionKey = $subscription->getKey();
            if (isset($carry[$subscriptionKey])) {
                throw new InvalidArgumentException("Subscription key '$subscriptionKey' is already defined.");
            }
            $carry[$subscriptionKey] = $subscription; // enforce consistent channel keys
            return $carry;
        }, []), SubscriptionInterface::class);
    }
}
