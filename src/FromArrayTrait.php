<?php

namespace Accordia\MessageBus;

trait FromArrayTrait
{
    /**
     * @param array $arrayState
     * @return MessageInterface
     */
    public static function fromArray(array $arrayState): MessageInterface
    {
        $classReflection = new \ReflectionClass(static::class);
        $classes = [ $classReflection ];
        $parent = $classReflection;
        while ($parent = $parent->getParentClass()) {
            $classes[] = $parent;
        }
        $valueFactories = [];
        foreach ($classes as $curClass) {
            $classProps = $curClass->getProperties();
            foreach ($classProps as $prop) {
                $propName = $prop->getName();
                $docBlock = $prop->getDocComment();
                if (!preg_match('/@var (.*)/', $docBlock, $matches)) {
                    continue;
                }
                $valueImplementor = $matches[1];
                if (!preg_match('/@buzz::fromArray->(.*)/', $docBlock, $matches)) {
                    continue;
                }
                $factoryMethod = $matches[1];
                if ($factoryMethod === '$ctor') {
                    $valueFactories[$propName] = function ($value) use ($valueImplementor) {
                        return new $valueImplementor($value);
                    };
                } else {
                    $valueFactories[$propName] = [ $valueImplementor, $factoryMethod ];
                }
            }
        }
        $ctorReflection = $classReflection->getMethod("__construct");
        $ctorArgs = [];
        foreach ($ctorReflection->getParameters() as $argumentReflection) {
            $argName = $argumentReflection->getName();
            if (isset($arrayState[$argName])) {
                if (isset($valueFactories[$argName])) {
                    $ctorArgs[] = call_user_func($valueFactories[$argName], $arrayState[$argName]);
                } else {
                    // missing factory annoation, throw exception or ignore?
                }
            } elseif ($argumentReflection->allowsNull()) {
                $ctorArgs[] = null;
            } else {
                throw new \Exception("Missing required value for array-key: $argName while constructing from array");
            }
        }
        return new static(...$ctorArgs);
    }
}
