<?php

namespace Accordia\Tests\MessageBus\Metadata;

use Accordia\MessageBus\Metadata\MetadataEnricherInterface;
use Accordia\MessageBus\Metadata\MetadataEnricherList;
use PHPUnit\Framework\TestCase;

final class MetadataEnricherListTest extends TestCase
{
    public function testPush()
    {
        $emptyList = new MetadataEnricherList;
        $enricherList = $emptyList->push(
            $this->getMockBuilder(MetadataEnricherInterface::CLASS)->getMock()
        );
        $this->assertCount(1, $enricherList);
        $this->assertTrue($emptyList->isEmpty());
    }
}
