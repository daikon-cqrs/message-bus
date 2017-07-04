<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Metadata;

final class CallbackMetadataEnricher implements MetadataEnricherInterface
{
    private $codeBlock;

    public function __construct(callable $codeBlock)
    {
        $this->codeBlock = $codeBlock;
    }

    public function enrich(Metadata $metadata): Metadata
    {
        return call_user_func($this->codeBlock, $metadata);
    }
}
