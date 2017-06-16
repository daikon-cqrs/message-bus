<?php

namespace Accordia\MessageBus;

trait ToArrayTrait
{
    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $data = [];
        $reflectionClass = new \ReflectionClass($this);
        $classProps = $reflectionClass->getProperties();
        foreach ($classProps as $prop) {
            $propName = $prop->getName();
            $propGetter = [ $this, "get".ucfirst($propName) ];
            if (!is_callable($propGetter)) {
                continue;
            }
            $propVal = call_user_func($propGetter);
            $toNative = [ $propVal, "toNative" ];
            $toArray = [ $propVal, "toArray" ];
            if (is_callable($toNative)) {
                $data[$propName] = call_user_func($toNative);
            } elseif (is_callable($toArray)) {
                $data[$propName] = call_user_func($toArray);
            } else {
                $data[$propName] = $propVal;
            }
        }
        return $data;
    }
}
