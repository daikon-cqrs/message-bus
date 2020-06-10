<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\DataStructure\TypedMap;
use Daikon\Interop\Assertion;

final class SubscriptionMap extends TypedMap
{
    public function __construct(iterable $subscriptions = [])
    {
        $mappedSubscriptions = [];
        /** @var SubscriptionInterface $subscription */
        foreach ($subscriptions as $subscription) {
            $subscriptionKey = $subscription->getKey();
            Assertion::keyNotExists(
                $mappedSubscriptions,
                $subscriptionKey,
                "Subscription key '$subscriptionKey' is already defined."
            );
            $mappedSubscriptions[$subscriptionKey] = $subscription;
        }

        $this->init($mappedSubscriptions, [SubscriptionInterface::class]);
    }
}
