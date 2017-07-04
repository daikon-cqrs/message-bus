<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\MessageBus\Metadata;

use Daikon\MessageBus\Metadata\CallbackMetadataEnricher;
use Daikon\MessageBus\Metadata\Metadata;
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
