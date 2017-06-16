<?php

namespace Accordia\MessageBus;

interface MessageInterface
{
    /**
     * @return mixed[]
     */
    public function toArray(): array;

    /**
     * @param mixed[] $data
     * @return MessageInterface
     */
    public static function fromArray(array $data): MessageInterface;
}
