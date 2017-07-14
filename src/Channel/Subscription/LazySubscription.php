<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Metadata\CallbackMetadataEnricher;
use Daikon\MessageBus\Metadata\Metadata;
use Daikon\MessageBus\Metadata\MetadataEnricherList;

final class LazySubscription implements SubscriptionInterface
{
    private $key;

    private $compositeSubscription;

    private $factoryCallback;

    public function __construct(
        string $key,
        callable $transport,
        callable $messageHandlers,
        callable $guard = null,
        MetadataEnricherList $metadataEnrichers = null
    ) {
        $this->key = $key;
        $this->factoryCallback = function () use ($transport, $messageHandlers, $guard, $metadataEnrichers) {
            return new Subscription(
                $this->key,
                $transport(),
                $messageHandlers(),
                $guard,
                ($metadataEnrichers ?? new MetadataEnricherList)->prepend(
                    new CallbackMetadataEnricher(function (Metadata $metadata): Metadata {
                        return $metadata->with(self::METADATA_KEY, $this->getKey());
                    })
                )
            );
        };
    }

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool
    {
        return $this->getSubscription()->publish($envelope);
    }

    public function receive(EnvelopeInterface $envelope): bool
    {
        return $this->getSubscription()->receive($envelope);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function getSubscription(): MessageHandlerList
    {
        if (!$this->compositeSubscription) {
            $this->compositeSubscription = call_user_func($this->factoryCallback);
            $this->factoryCallback = null;
        }
        return $this->compositeSubscription;
    }
}
