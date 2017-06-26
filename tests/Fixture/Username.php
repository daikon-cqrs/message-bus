<?php

namespace Daikon\Tests\MessageBus\Fixture;

final class Username
{
    private $username;

    public function __construct(string $username = "")
    {
        $this->username = $username;
    }

    public function toNative(): string
    {
        return $this->username;
    }
}
