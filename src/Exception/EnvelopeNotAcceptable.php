<?php declare(strict_types=1);
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Daikon\MessageBus\Exception;

use Daikon\Interop\RuntimeException;

final class EnvelopeNotAcceptable extends RuntimeException implements MessageBusException
{
    public const SUBSCRIPTION_KEY_MISSING = 6000;
    public const SUBSCRIPTION_KEY_UNEXPECTED = 6001;
    public const CHANNEL_KEY_MISSING = 5000;
    public const CHANNEL_KEY_UNEXPECTED = 5001;
    public const UNPARSEABLE = 7000;
}
