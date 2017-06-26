<?php

namespace Daikon\Tests\MessageBus\Fixture;

final class UserId
{
    private $userId;

    public function __construct(string $userId = "")
    {
        $this->userId = $userId;
    }

    public function toNative(): string
    {
        return $this->userId;
    }
}
