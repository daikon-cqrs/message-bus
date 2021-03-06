<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Channel;

use Closure;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionInterface;
use Daikon\MessageBus\Channel\Subscription\SubscriptionMap;
use Daikon\MessageBus\Exception\EnvelopeNotAcceptable;
use Daikon\MessageBus\Exception\SubscriptionUnknown;
use Daikon\Metadata\MetadataInterface;
use Daikon\Metadata\MetadataEnricherInterface;
use Daikon\Metadata\MetadataEnricherList;

final class Channel implements ChannelInterface
{
    private string $key;

    private SubscriptionMap $subscriptions;

    private Closure $guard;

    private MetadataEnricherList $metadataEnrichers;

    public function __construct(
        string $key,
        SubscriptionMap $subscriptions,
        Closure $guard = null,
        MetadataEnricherList $metadataEnrichers = null
    ) {
        $this->key = $key;
        $this->subscriptions = $subscriptions;
        $this->guard = $guard ?? fn(): bool => true;
        $metadataEnrichers = $metadataEnrichers ?? new MetadataEnricherList;
        $this->metadataEnrichers = $metadataEnrichers->enrichWith(self::METADATA_KEY, $this->key);
    }

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): void
    {
        $envelope = $this->enrichMetadata($envelope);
        if ($this->accepts($envelope)) {
            foreach ($this->subscriptions as $subscription) {
                $subscription->publish($envelope, $messageBus);
            }
        }
    }

    public function receive(EnvelopeInterface $envelope): void
    {
        $this->verify($envelope);
        $subscriptionKey = (string)$envelope->getMetadata()->get(SubscriptionInterface::METADATA_KEY);
        if (!$this->subscriptions->has($subscriptionKey)) {
            throw new SubscriptionUnknown(
                "Channel '{$this->key}' has no subscription '$subscriptionKey' and thus ".
                "Envelope '{$envelope->getUuid()->toString()}' cannot be handled."
            );
        }
        /** @var SubscriptionInterface $subscription */
        $subscription = $this->subscriptions->get($subscriptionKey);
        $subscription->receive($envelope);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function enrichMetadata(EnvelopeInterface $envelope): EnvelopeInterface
    {
        return $envelope->withMetadata(array_reduce(
            $this->metadataEnrichers->unwrap(),
            function (MetadataInterface $metadata, MetadataEnricherInterface $metadataEnricher): MetadataInterface {
                return $metadataEnricher->enrich($metadata);
            },
            $envelope->getMetadata()
        ));
    }

    private function accepts(EnvelopeInterface $envelope): bool
    {
        return (bool)($this->guard)($envelope);
    }

    private function verify(EnvelopeInterface $envelope): void
    {
        $metadata = $envelope->getMetadata();
        if (!$metadata->has(self::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Channel key '".self::METADATA_KEY."' missing in metadata of Envelope ".
                "'{$envelope->getUuid()->toString()}' received on channel '{$this->key}'.",
                EnvelopeNotAcceptable::CHANNEL_KEY_MISSING
            );
        }
        $channelKey = $metadata->get(self::METADATA_KEY);
        if ($channelKey !== $this->key) {
            throw new EnvelopeNotAcceptable(
                "Channel '{$this->key}' inadvertently received Envelope ".
                "'{$envelope->getUuid()->toString()}' for channel '$channelKey'.",
                EnvelopeNotAcceptable::CHANNEL_KEY_UNEXPECTED
            );
        }
        if (!$metadata->has(SubscriptionInterface::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Subscription key '".SubscriptionInterface::METADATA_KEY."' missing in metadata of ".
                "Envelope '{$envelope->getUuid()->toString()}' received on channel '{$this->key}'.",
                EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING
            );
        }
    }
}
