<?php

namespace Accordia\MessageBus\Metadata;

use Ds\Map;

final class Metadata implements \IteratorAggregate, \Countable
{
    /**
     * @var Map
     */
    private $compositeMap;

    /**
     * @param mixed[] $metadata
     * @return Metadata
     */
    public static function fromArray(array $metadata): Metadata
    {
        return new self($metadata);
    }

    /**
     * @return Metadata
     */
    public static function makeEmpty(): Metadata
    {
        return new self;
    }

    /**
     * @param mixed[] $metadata
     */
    private function __construct(array $metadata = [])
    {
        $this->compositeMap = new Map($metadata);
    }

    /**
     * @param Metadata $metadata
     * @return bool
     */
    public function equals(Metadata $metadata)
    {
        foreach ($metadata as $key => $value) {
            if (!$this->has($key) || $this->get($key) !== $value) {
                return false;
            }
        }
        return $metadata->count() === $this->count();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->compositeMap->hasKey($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Metadata
     */
    public function with(string $key, $value): Metadata
    {
        $copy = clone $this;
        $copy->compositeMap->put($key, $value);
        return $copy;
    }

    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $this->compositeMap->get($key) : $default;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->compositeMap->isEmpty();
    }

    /**
     * @return \Iterator
     */
    public function getIterator(): \Iterator
    {
        return $this->compositeMap->getIterator();
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->compositeMap->count();
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->compositeMap->toArray();
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    private function __clone()
    {
        $this->compositeMap = clone $this->compositeMap;
    }
}
