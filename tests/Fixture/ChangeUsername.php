<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\Tests\MessageBus\Fixture;

use Daikon\MessageBus\MessageInterface;

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

    public function toNative(): array
    {
        return [
            "identifier" => $this->identifier->toNative(),
            "knownRevision" => $this->knownRevision->toNative(),
            "username" => $this->username->toNative()
        ];
    }

    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            new UserId($state["identifier"]),
            new KnownRevision((int)$state["knownRevision"]),
            new Username($state["username"])
        );
    }
}
