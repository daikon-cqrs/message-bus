<?php

namespace Daikon\MessageBus\Channel\Subscription;

use Daikon\MessageBus\Channel\Subscription\MessageHandler\MessageHandlerList;
use Daikon\MessageBus\Channel\Subscription\Transport\TransportInterface;
use Daikon\MessageBus\EnvelopeInterface;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\MessageBusInterface;
use Daikon\MessageBus\Metadata\CallbackMetadataEnricher;
use Daikon\MessageBus\Metadata\Metadata;
use Daikon\MessageBus\Metadata\MetadataEnricherInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherList;

final class Subscription implements SubscriptionInterface
{
    /**
     * @var TransportInterface
     */
    private $transport;

    /**
     * @var MessageHandlerList
     */
    private $messageHandlers;

    /**
     * @var callable
     */
    private $guard;

    /**
     * @var MetadataEnricherList
     */
    private $metadataEnrichers;

    /**
     * @var string
     */
    private $key;

    /**
     * @param string $key
     * @param TransportInterface $transport
     * @param MessageHandlerList $messageHandlers
     * @param callable|null $guard
     * @param MetadataEnricherList|null $metadataEnrichers
     */
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
        $this->guard = $guard;
        $this->metadataEnrichers = ($metadataEnrichers ?? new MetadataEnricherList)
            ->push(new CallbackMetadataEnricher(function (Metadata $metadata): Metadata {
                return $metadata->with(self::METADATA_KEY, $this->getKey());
            }));
    }

    /**
     * @param EnvelopeInterface $envelope
     * @param MessageBusInterface $messageBus
     * @return bool
     */
    public function publish(EnvelopeInterface $envelope, MessageBusInterface $messageBus): bool
    {
        $envelope = $this->enrichMetadata($envelope);
        return $this->accepts($envelope) && $this->transport->send($envelope, $messageBus);
    }

    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    public function receive(EnvelopeInterface $envelope): bool
    {
        $this->verify($envelope);
        $messageWasHandled = false;
        foreach ($this->messageHandlers as $messageHandler) {
            if ($messageHandler->handle($envelope) && !$messageWasHandled) {
                $messageWasHandled = true;
            }
        }
        return $messageWasHandled;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param EnvelopeInterface $envelope
     * @return EnvelopeInterface
     */
    private function enrichMetadata(EnvelopeInterface $envelope): EnvelopeInterface
    {
        return $envelope->withMetadata(array_reduce(
            $this->metadataEnrichers->toArray(),
            function (Metadata $metadata, MetadataEnricherInterface $metadataEnricher) {
                return $metadataEnricher->enrich($metadata);
            },
            $envelope->getMetadata()
        ));
    }

    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     */
    private function accepts(EnvelopeInterface $envelope)
    {
        if ($this->guard) {
            return (bool)call_user_func($this->guard, $envelope);
        }
        return true;
    }

    /**
     * @param EnvelopeInterface $envelope
     * @throws EnvelopeNotAcceptable
     */
    private function verify(EnvelopeInterface $envelope)
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
