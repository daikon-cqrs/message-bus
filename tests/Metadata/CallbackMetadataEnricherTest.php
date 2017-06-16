<?php

namespace Accordia\Tests\MessageBus\Metadata;

use Accordia\MessageBus\Metadata\CallbackMetadataEnricher;
use Accordia\MessageBus\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

final class CallbackMetadataEnricherTest extends TestCase
{
    public function testEnrich()
    {
        $metadata = (new CallbackMetadataEnricher(function (Metadata $metadata) {
            return $metadata->with("message", "hello world");
        }))->enrich(Metadata::makeEmpty());
        $this->assertEquals($metadata->message, "hello world");
    }
}
