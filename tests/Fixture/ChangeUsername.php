<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus\Fixture;

use Daikon\MessageBus\MessageInterface;

final class ChangeUsername implements MessageInterface
{
    private UserId $userId;

    private KnownRevision $knownRevision;

    private Username $username;
    
    public function __construct(UserId $userId, KnownRevision $knownRevision, Username $username)
    {
        $this->userId = $userId;
        $this->knownRevision = $knownRevision;
        $this->username = $username;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
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
            'userId' => $this->userId->toNative(),
            'knownRevision' => $this->knownRevision->toNative(),
            'username' => $this->username->toNative()
        ];
    }

    /** @param array $state */
    public static function fromNative($state): MessageInterface
    {
        return new self(
            new UserId($state['userId']),
            new KnownRevision((int)$state['knownRevision']),
            new Username($state['username'])
        );
    }
}
