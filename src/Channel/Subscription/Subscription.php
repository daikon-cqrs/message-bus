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
use Daikon\MessageBus\Channel\Subscription\Transport\TransportInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Metadata\CallbackMetadataEnricher;
use Daikon\MessageBus\Metadata\MetadataInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherList;

final class Subscription implements SubscriptionInterface
{
    /** @var TransportInterface */
    private $transport;

    /** @var MessageHandlerList */
    private $messageHandlers;

    /** @var callable */
    private $guard;

    /** @var MetadataEnricherList */
    private $metadataEnrichers;

    /** @var string */
    private $key;

    public function __construct(
        string $key,
        TransportInterface $transport,
        MessageHandlerList $messageHandlers,
        callable $guard = null,
        MetadataEnricherList $metadataEnrichers = null
    ) {
        $this->key = $key;
        $this->transport = $transport;
        $this->messageHandlers = $messageHandlers;
        $this->guard = $guard ?? function (EnvelopeInterface $envelope): bool {
            return true;
        };
        $metadataEnrichers = $metadataEnrichers ?? new MetadataEnricherList;
        $this->metadataEnrichers = $metadataEnrichers->prependDefaultEnricher(self::METADATA_KEY, $this->key);
    }

    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): void
    {
        $envelope = $this->enrichMetadata($envelope);
        if ($this->accepts($envelope)) {
            $this->transport->send($envelope, $messageBus);
        }
    }

    public function receive(EnvelopeInterface $envelope): void
    {
        $this->verify($envelope);
        foreach ($this->messageHandlers as $messageHandler) {
            $messageHandler->handle($envelope);
        }
    }

    public function getKey(): string
    {
        return $this->key;
    }

    private function enrichMetadata(EnvelopeInterface $envelope): EnvelopeInterface
    {
        return $envelope->withMetadata(array_reduce(
            $this->metadataEnrichers->toNative(),
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
                "Subscription key '".self::METADATA_KEY."' missing in metadata of Envelope '{$envelope->getUuid()}' ".
                "received by subscription '{$this->key}'.",
                EnvelopeNotAcceptable::SUBSCRIPTION_KEY_MISSING
            );
        }
        $subscriptionKey = $metadata->get(self::METADATA_KEY);
        if ($subscriptionKey !== $this->key) {
            throw new EnvelopeNotAcceptable(
                "Subscription '{$this->key}' inadvertently received Envelope '{$envelope->getUuid()}' ".
                "for subscription '$subscriptionKey'.",
                EnvelopeNotAcceptable::SUBSCRIPTION_KEY_UNEXPECTED
            );
        }
    }
}
