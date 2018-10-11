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

final class Metadata implements MetadataInterface
{
    private $compositeMap;

    public static function fromArray(array $metadata): MetadataInterface
    {
        return new self($metadata);
    }

    public static function makeEmpty(): MetadataInterface
    {
        return new self;
    }

    private function __construct(array $metadata = [])
    {
        $this->compositeMap = new Map($metadata);
    }

    public function equals(MetadataInterface $metadata)
    {
        foreach ($metadata as $key => $value) {
            if (!$this->has($key) || $this->get($key) !== $value) {
                return false;
            }
        }
        return $metadata->count() === $this->count();
    }

    public function has(string $key): bool
    {
        return $this->compositeMap->hasKey($key);
    }

    public function with(string $key, $value): MetadataInterface
    {
        $copy = clone $this;
        $copy->compositeMap->put($key, $value);
        return $copy;
    }

    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->compositeMap->get($key) : $default;
    }

    public function isEmpty()
    {
        return $this->compositeMap->isEmpty();
    }

    public function getIterator(): \Iterator
    {
        return $this->compositeMap->getIterator();
    }

    public function count(): int
    {
        return $this->compositeMap->count();
    }

    public function toArray(): array
    {
        return $this->compositeMap->toArray();
    }

    public function __get(string $key)
    {
        return $this->get($key);
    }

    private function __clone()
    {
        $this->compositeMap = clone $this->compositeMap;
    }
}
