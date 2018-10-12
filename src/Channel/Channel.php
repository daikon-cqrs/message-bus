<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Channel;

use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\Error\SubscriptionUnknown;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Metadata\CallbackMetadataEnricher;
use Daikon\MessageBus\Metadata\MetadataInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherList;

final class Channel implements ChannelInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var SubscriptionMap
     */
    private $subscriptions;

    /**
     * @var callable
     */
    private $guard;

    /**
     * @var MetadataEnricherList
     */
    private $metadataEnrichers;

    public function __construct(
        string $key,
        SubscriptionMap $subscriptions,
        callable $guard = null,
        MetadataEnricherList $metadataEnrichers = null
    ) {
        $this->key = $key;
        $this->subscriptions = $subscriptions;
        $this->guard = $guard ?? function (EnvelopeInterface $envelope): bool {
            return true;
        };
        $this->metadataEnrichers = $metadataEnrichers
            ?? MetadataEnricherList::defaultEnrichers(self::METADATA_KEY, $this->key);
    }

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool
    {
        $envelope = $this->enrichMetadata($envelope);
        if (!$this->accepts($envelope)) {
            return false;
        }
        $messageWasPublished = false;
        foreach ($this->subscriptions as $subscription) {
            if ($subscription->publish($envelope, $messageBus) && !$messageWasPublished) {
                $messageWasPublished = true;
            }
        }
        return $messageWasPublished;
    }

    public function receive(EnvelopeInterface $envelope): bool
    {
        $this->verify($envelope);
        $subscriptionKey = $envelope->getMetadata()->get(SubscriptionInterface::METADATA_KEY);
        if (!$this->subscriptions->has($subscriptionKey)) {
            throw new SubscriptionUnknown(
                "Channel '{$this->key}' has no subscription '$subscriptionKey' and thus ".
                "Envelope '{$envelope->getUuid()}' cannot be handled."
            );
        }
        $subscription = $this->subscriptions->get($subscriptionKey); /* @var $subscription SubscriptionInterface */
        return $subscription->receive($envelope);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function enrichMetadata(EnvelopeInterface $envelope): EnvelopeInterface
    {
        return $envelope->withMetadata(array_reduce(
            $this->metadataEnrichers->toArray(),
            function (MetadataInterface $metadata, MetadataEnricherInterface $metadataEnricher): MetadataInterface {
                return $metadataEnricher->enrich($metadata);
            },
            $envelope->getMetadata()
        ));
    }

    private function accepts(EnvelopeInterface $envelope): bool
    {
        return (bool)call_user_func($this->guard, $envelope);
    }

    private function verify(EnvelopeInterface $envelope): void
    {
        $metadata = $envelope->getMetadata();
        if (!$metadata->has(self::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Channel key '".self::METADATA_KEY."' missing in metadata of Envelope '{$envelope->getUuid()}' ".
                "received on channel '{$this->key}'.",
                EnvelopeNotAcceptable::CHANNEL_KEY_MISSING
            );
        }
        $channelKey = $metadata->get(self::METADATA_KEY);
        if ($channelKey !== $this->key) {
            throw new EnvelopeNotAcceptable(
                "Channel '{$this->key}' inadvertently received Envelope '{$envelope->getUuid()}' ".
                "for channel '$channelKey'.",
                EnvelopeNotAcceptable::CHANNEL_KEY_UNEXPECTED
            );
        }
        if (!$metadata->has(SubscriptionInterface::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Subscription key '".SubscriptionInterface::METADATA_KEY."' missing in metadata of ".
                "Envelope '{$envelope->getUuid()}' received on channel '{$this->key}'.",
                EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING
            );
        }
    }
}
