<?php

declare(strict_types=1);

namespace SquidIT\Event\Listener;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionUnionType;
use RuntimeException;
use SquidIT\Event\Event\EventInterface;
use UnexpectedValueException;

class ListenerInfo
{
    public const int DEFAULT_PRIORITY                   = 0;
    public const string HANDLER_PROPERTY_NAME           = '__invoke';
    public const string COROUTINE_HANDLER_PROPERTY_NAME = 'coroutineExecute';

    public readonly bool $useCoroutine;

    /**
     * @param class-string $eventName
     * @param class-string $listenerName
     *
     * @throws ReflectionException|UnexpectedValueException
     */
    public function __construct(
        public readonly string $eventName,
        public readonly string $listenerName,
        public readonly int $priority = self::DEFAULT_PRIORITY,
    ) {
        if (class_exists($eventName) === false) {
            throw new UnexpectedValueException(sprintf(
                'The supplied eventName "%s" class could not be found',
                $eventName
            ));
        }

        if (is_a($listenerName, ListenerInterface::class, true) === false) {
            throw new UnexpectedValueException(sprintf(
                'The supplied listener "%s" does not implement %s',
                $listenerName,
                ListenerInterface::class
            ));
        }

        $reflectionClass = new ReflectionClass($listenerName);
        $actionMethod    = $reflectionClass->implementsInterface(CoroutineListenerInterface::class) ?
            self::COROUTINE_HANDLER_PROPERTY_NAME : self::HANDLER_PROPERTY_NAME;

        $reflectionMethod    = $reflectionClass->getMethod($actionMethod);
        $reflectionParameter = $reflectionMethod->getParameters()[0];
        $parameterUnionType  = $reflectionParameter->getType();

        if (
            $parameterUnionType instanceof ReflectionUnionType === false
            || count($parameterUnionType->getTypes()) !== 2
        ) {
            throw new UnexpectedValueException(sprintf(
                'The supplied listener "%s" %s() method is missing the required union type definition: [EventClassName]|%s',
                $listenerName,
                $actionMethod,
                EventInterface::class
            ));
        }

        $eventClassName = null;

        /** @var ReflectionNamedType $reflectionNamedType */
        foreach ($parameterUnionType->getTypes() as $reflectionNamedType) {
            $typeName = $reflectionNamedType->getName();

            if ($typeName !== EventInterface::class) {
                $eventClassName = $typeName;
            }
        }

        if ($eventClassName === null || is_a($eventName, $eventClassName, true) === false) {
            throw new UnexpectedValueException(sprintf(
                'The supplied listener "%s" is not compatible with event: %s',
                $listenerName,
                $eventName
            ));
        }

        $this->useCoroutine = is_a($listenerName, CoroutineListenerInterface::class, true);

        if ($this->useCoroutine === true && extension_loaded('Swow') === false) {
            throw new RuntimeException(sprintf(
                'Unable to setup listener "%s", the Swow Coroutine extension is not available',
                $listenerName,
            ));
        }
    }
}
