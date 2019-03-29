<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Metadata;

use Daikon\DataStructure\TypedListTrait;

final class MetadataEnricherList implements \IteratorAggregate, \Countable
{
    use TypedListTrait;

    public function prependDefaultEnricher(string $namespace, string $value): self
    {
        return $this->unshift(
            new CallbackMetadataEnricher(
                function (MetadataInterface $metadata) use ($namespace, $value): MetadataInterface {
                    return $metadata->with($namespace, $value);
                }
            )
        );
    }

    public function __construct(array $enrichers = [])
    {
        $this->init($enrichers, MetadataEnricherInterface::class);
    }
}
