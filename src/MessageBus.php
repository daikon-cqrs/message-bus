<?php

namespace Accordia\MessageBus;

use Accordia\MessageBus\Channel\ChannelInterface;
use Accordia\MessageBus\Channel\ChannelMap;
use Accordia\MessageBus\Error\ChannelUnknown;
use Accordia\MessageBus\Error\EnvelopeNotAcceptable;
use Accordia\MessageBus\Metadata\Metadata;
use Accordia\MessageBus\Metadata\MetadataEnricherInterface;
use Accordia\MessageBus\Metadata\MetadataEnricherList;

final class MessageBus implements MessageBusInterface
{
    /**
     * @var ChannelMap
     */
    private $channelMap;

    /**
     * @var MetadataEnricherList
     */
    private $metadataEnrichers;

    /**
     * @var string
     */
    private $envelopeType;

    /**
     * @param ChannelMap $channelMap
     * @param MetadataEnricherList|null $metadataEnrichers
     * @param string|null $envelopeType
     */
    public function __construct(
        ChannelMap $channelMap,
        MetadataEnricherList $metadataEnrichers = null,
        string $envelopeType = null
    ) {
        $this->channelMap = $channelMap;
        $this->metadataEnrichers = $metadataEnrichers ?? new MetadataEnricherList;
        $this->envelopeType = $envelopeType ?? Envelope::CLASS;
    }

    /**
     * @param MessageInterface $message
     * @param string $channel
     * @param Metadata|null $metadata
     * @return bool
     * @throws ChannelUnknown
     */
    public function publish(MessageInterface $message, string $channel, Metadata $metadata = null): bool
    {
        if (!$this->channelMap->has($channel)) {
            throw new ChannelUnknown("Channel '$channel' has not been registered on message bus.");
        }
        $metadata = $this->enrichMetadata($metadata ?? Metadata::makeEmpty());
        $envelopeType = $this->envelopeType; /* @var $envelopeType EnvelopeInterface */
        $envelope = $envelopeType::wrap($message, $metadata);
        $channel = $this->channelMap->get($channel); /* @var $channel ChannelInterface */
        return $channel->publish($envelope, $this);
    }

    /**
     * @param EnvelopeInterface $envelope
     * @return bool
     * @throws ChannelUnknown
     */
    public function receive(EnvelopeInterface $envelope): bool
    {
        $this->verify($envelope);
        $channelKey = $envelope->getMetadata()->get(ChannelInterface::METADATA_KEY);
        if (!$this->channelMap->has($channelKey)) {
            throw new ChannelUnknown("Channel '$channelKey' has not been registered on message bus.");
        }
        $channel = $this->channelMap->get($channelKey); /* @var $channel ChannelInterface */
        return $channel->receive($envelope);
    }

    /**
     * @param Metadata $metadata
     * @return Metadata
     */
    private function enrichMetadata(Metadata $metadata): Metadata
    {
        return array_reduce(
            $this->metadataEnrichers->toArray(),
            function (Metadata $metadata, MetadataEnricherInterface $metadataEnricher) {
                return $metadataEnricher->enrich($metadata);
            },
            $metadata
        );
    }

    /**
     * @param EnvelopeInterface $envelope
     * @throws EnvelopeNotAcceptable
     */
    private function verify(EnvelopeInterface $envelope)
    {
        $metadata = $envelope->getMetadata();
        if (!$metadata->has(ChannelInterface::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Channel key '".ChannelInterface::METADATA_KEY."' missing in metadata of ".
                "Envelope '{$envelope->getUuid()}' received on message bus.",
                EnvelopeNotAcceptable::CHANNEL_KEY_MISSING
            );
        }
    }
}
