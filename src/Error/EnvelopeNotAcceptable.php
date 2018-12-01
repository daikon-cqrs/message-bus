<?php
/**
 * This file is part of the daikon-cqrs/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Error;

final class EnvelopeNotAcceptable extends \Exception implements ErrorInterface
{
    const SUBSCRIPTION_KEY_MISSING = 6000;
    const SUBSCRIPTION_KEY_UNEXPECTED = 6001;
    const CHANNEL_KEY_MISSING = 5000;
    const CHANNEL_KEY_UNEXPECTED = 5001;
    const UNPARSEABLE = 7000;
}
