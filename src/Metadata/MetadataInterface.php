<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Metadata;

use Ds\Map;

interface MetadataInterface extends \IteratorAggregate, \Countable
{
    public static function fromArray(array $metadata): MetadataInterface;

    public static function makeEmpty(): MetadataInterface;

    public function equals(MetadataInterface $metadata): bool;

    public function has(string $key): bool;

    public function with(string $key, $value): MetadataInterface;

    public function get(string $key, $default = null);

    public function isEmpty(): bool;

    public function getIterator(): \Traversable;

    public function count(): int;

    public function toArray(): array;

    public function __get(string $key);
}
