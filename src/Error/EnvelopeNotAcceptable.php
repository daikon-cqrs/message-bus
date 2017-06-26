<?php

namespace Daikon\MessageBus\Error;

final class EnvelopeNotAcceptable extends \Exception implements ErrorInterface
{
    const SUBSCRIPTION_KEY_MISSING = 6000;
    const SUBSCRIPTION_KEY_UNEXPECTED = 6001;
    const CHANNEL_KEY_MISSING = 5000;
    const CHANNEL_KEY_UNEXPECTED = 5001;
}
