<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\DataStructure\TypedMap;

final class SubscriptionMap extends TypedMap
{
    public function __construct(iterable $subscriptions = [])
    {
        $this->init($subscriptions, [SubscriptionInterface::class]);
    }
}
