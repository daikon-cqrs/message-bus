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

    public static function defaultEnrichers(string $namespace, string $value)
    {
        $enricher_callback = function (MetadataInterface $metadata) use ($namespace, $value): MetadataInterface {
            return $metadata->with($namespace, $value);
        };
        return (new MetadataEnricherList)->prepend(
            new CallbackMetadataEnricher($enricher_callback)
        );
    }

    public function __construct(array $enrichers = [])
    {
        $this->init($enrichers, MetadataEnricherInterface::CLASS);
    }
}
