<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus;

use Daikon\MessageBus\Channel\ChannelInterface;
use Daikon\MessageBus\Channel\ChannelMap;
use Daikon\MessageBus\Error\ChannelUnknown;
use Daikon\MessageBus\Error\EnvelopeNotAcceptable;
use Daikon\Metadata\MetadataInterface;
use Daikon\Metadata\Metadata;
use Daikon\Metadata\MetadataEnricherInterface;
use Daikon\Metadata\MetadataEnricherList;

final class MessageBus implements MessageBusInterface
{
    /** @var ChannelMap */
    private $channelMap;

    /** @var MetadataEnricherList */
    private $metadataEnrichers;

    /** @var string */
    private $envelopeType;

    public function __construct(
        ChannelMap $channelMap,
        MetadataEnricherList $metadataEnrichers = null,
        string $envelopeType = null
    ) {
        $this->channelMap = $channelMap;
        $this->metadataEnrichers = $metadataEnrichers ?? new MetadataEnricherList;
        $this->envelopeType = $envelopeType ?? Envelope::class;
    }

    public function publish(MessageInterface $message, string $channel, MetadataInterface $metadata = null): void
    {
        if (!$this->channelMap->has($channel)) {
            throw new ChannelUnknown("Channel '$channel' has not been registered on message bus.");
        }
        $metadata = $this->enrichMetadata($metadata ?? Metadata::makeEmpty());
        $envelopeType = $this->envelopeType;
        $envelope = $envelopeType::wrap($message, $metadata);
        $channel = $this->channelMap->get($channel);
        $channel->publish($envelope, $this);
    }

    public function receive(EnvelopeInterface $envelope): void
    {
        $this->verify($envelope);
        $channelKey = (string)$envelope->getMetadata()->get(ChannelInterface::METADATA_KEY);
        if (!$this->channelMap->has($channelKey)) {
            throw new ChannelUnknown("Channel '$channelKey' has not been registered on message bus.");
        }
        $channel = $this->channelMap->get($channelKey);
        $channel->receive($envelope);
    }

    private function enrichMetadata(MetadataInterface $metadata): MetadataInterface
    {
        return array_reduce(
            $this->metadataEnrichers->toNative(),
            function (MetadataInterface $metadata, MetadataEnricherInterface $metadataEnricher): MetadataInterface {
                return $metadataEnricher->enrich($metadata);
            },
            $metadata
        );
    }

    private function verify(EnvelopeInterface $envelope): void
    {
        $metadata = $envelope->getMetadata();
        if (!$metadata->has(ChannelInterface::METADATA_KEY)) {
            throw new EnvelopeNotAcceptable(
                "Channel key '".ChannelInterface::METADATA_KEY."' missing in metadata of ".
                "Envelope '{$envelope->getUuid()->toString()}' received on message bus.",
                EnvelopeNotAcceptable::CHANNEL_KEY_MISSING
            );
        }
    }
}
