<?php

namespace Accordia\MessageBus\Metadata;

use Accordia\DataStructures\TypedListTrait;

final class MetadataEnricherList implements \IteratorAggregate, \Countable
{
    use TypedListTrait;

    /**
     * @param MetadataEnricherInterface[] $enrichers
     */
    public function __construct(array $enrichers = [])
    {
        $this->init($enrichers, MetadataEnricherInterface::CLASS);
    }
}
