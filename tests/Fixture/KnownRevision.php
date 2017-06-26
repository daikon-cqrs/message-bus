<?php

namespace Daikon\Tests\MessageBus\Fixture;

final class KnownRevision
{
    private $revision;

    public function __construct(int $revision)
    {
        $this->revision = $revision;
    }

    public function toNative(): int
    {
        return $this->revision;
    }
}
