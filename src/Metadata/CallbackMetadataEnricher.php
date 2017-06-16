<?php

namespace Accordia\MessageBus\Metadata;

final class CallbackMetadataEnricher implements MetadataEnricherInterface
{
    /**
     * @var callable
     */
    private $codeBlock;

    /**
     * @param callable $codeBlock
     */
    public function __construct(callable $codeBlock)
    {
        $this->codeBlock = $codeBlock;
    }

    /**
     * @param Metadata $metadata
     * @return Metadata
     */
    public function enrich(Metadata $metadata): Metadata
    {
        return call_user_func($this->codeBlock, $metadata);
    }
}
