<?php

namespace Accordia\Tests\MessageBus\Fixture;

use Accordia\MessageBus\MessageInterface;

final class ChangeUsername implements MessageInterface
{
    private $username;

    private $identifier;

    private $knownRevision;

    public function __construct(UserId $identifier, KnownRevision $knownRevision, Username $username)
    {
        $this->username = $username;
        $this->knownRevision = $knownRevision;
        $this->identifier = $identifier;
    }

    public function getIdentifier(): UserId
    {
        return $this->identifier;
    }

    public function getKnownRevision(): KnownRevision
    {
        return $this->knownRevision;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function toArray(): array
    {
        return [
            "identifier" => $this->identifier->toNative(),
            "knownRevision" => $this->knownRevision->toNative(),
            "username" => $this->username->toNative()
        ];
    }

    public static function fromArray(array $data): MessageInterface
    {
        return new self(
            new UserId($data["identifier"]),
            new KnownRevision((int)$data["knownRevision"]),
            new Username($data["username"])
        );
    }
}
