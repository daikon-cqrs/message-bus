<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\Tests\MessageBus\Fixture;

final class UserId
{
    /** @var string */
    private $userId;

    public function __construct(string $userId = '')
    {
        $this->userId = $userId;
    }

    public function toNative(): string
    {
        return $this->userId;
    }
}
