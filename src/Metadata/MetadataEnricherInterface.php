<?php

namespace Accordia\MessageBus\Metadata;

interface MetadataEnricherInterface
{
    /**
     * @param Metadata $metadata
     * @return Metadata
     */
    public function enrich(Metadata $metadata): Metadata;
}
