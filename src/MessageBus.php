<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus;

use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Error\ChannelUnknown;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\MessageBus\Metadata\Metadata;
use Daikon\MessageBus\Metadata\MetadataEnricherInterface;
use Daikon\MessageBus\Metadata\MetadataEnricherList;

final class MessageBus implements MessageBusInterface
{
    private $channelMap;

    private $metadataEnrichers;

    private $envelopeType;

    public function __construct(
        ChannelMap $channelMap,
        MetadataEnricherList $metadataEnrichers = null,
        string $envelopeType = null
    ) {
        $this->channelMap = $channelMap;
        $this->metadataEnrichers = $metadataEnrichers ?? new MetadataEnricherList;
        $this->envelopeType = $envelopeType ?? Envelope::CLASS;
    }

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
