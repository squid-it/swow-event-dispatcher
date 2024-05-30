<?php

declare(strict_types=1);

namespace SquidIT\Event;

use Ds\PriorityQueue;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;
use SquidIT\Container\Mason\ContainerMasonInterface;
use SquidIT\Event\Exceptions\ListenersAlreadyRegisteredException;
use SquidIT\Event\Exceptions\NoListenersFoundException;
use SquidIT\Event\Listener\ListenerInfo;
use SquidIT\Event\Listener\ListenerInterface;
use Throwable;
use UnexpectedValueException;

class ListenerProvider implements ListenerProviderInterface
{
    /** @var array<class-string, array<int, ListenerInfo>> */
    public array $listenerListByEvent = [];

    /** @var array<string, ListenerInfo> */
    private array $listenerList = [];

    public function __construct(
        private readonly ContainerMasonInterface $container
    ) {}

    /**
     * @param class-string $eventName
     * @param class-string $listener
     *
     * @throws ReflectionException|UnexpectedValueException
     * @throws ListenersAlreadyRegisteredException
     */
    public function addEventListener(
        string $eventName,
        string $listener,
        int $priority = ListenerInfo::DEFAULT_PRIORITY
    ): void {
        $listenerEventKey = sprintf('%s.%s', $eventName, $listener);

        if (array_key_exists($listenerEventKey, $this->listenerList)) {
            throw new ListenersAlreadyRegisteredException(
                sprintf('Listener "%s" is already registered for event: "%s"', $listener, $eventName)
            );
        }

        $this->listenerList[$listenerEventKey] = new ListenerInfo($eventName, $listener, $priority);
    }

    /**
     * {@inheritDoc}
     *
     * @throws NoListenersFoundException|Throwable
     *
     * @return PriorityQueue<ListenerInterface>
     */
    public function getListenersForEvent(object $event): iterable
    {
        $listenerInfoList = $this->findExistingList($event);
        $listenerInfoList = $listenerInfoList ?? $this->createListenerListForEvent($event);

        return $this->createListenerQueue($listenerInfoList);
    }

    /**
     * @return array<ListenerInfo>|null
     */
    private function findExistingList(object $event): ?array
    {
        $eventClass = get_class($event);

        return $this->listenerListByEvent[$eventClass] ?? null;
    }

    /**
     * @throws NoListenersFoundException|Throwable
     *
     * @return array<int, ListenerInfo>
     */
    private function createListenerListForEvent(object $event): array
    {
        $listeners = [];
        $className = get_class($event);

        foreach ($this->listenerList as $listener) {
            if ($event instanceof $listener->eventName) {
                $listeners[] = $listener;
            }
        }

        if (count($listeners) === 0) {
            throw new NoListenersFoundException(
                sprintf('No event listeners found for event: %s.', $className)
            );
        }

        $this->listenerListByEvent[$className] = $listeners;

        return $listeners;
    }

    /**
     * @param array<int, ListenerInfo> $listenerList
     *
     * @throws Throwable
     *
     * @return PriorityQueue<ListenerInterface>
     */
    private function createListenerQueue(array $listenerList): PriorityQueue
    {
        $eventListenerQueue = new PriorityQueue();

        foreach ($listenerList as $listenerInfo) {
            if ($listenerInfo->useCoroutine) {
                $listener = $this->container->getNew($listenerInfo->listenerName);
            } else {
                $listener = $this->container->get($listenerInfo->listenerName);
            }

            /** @var ListenerInterface $listener */
            $eventListenerQueue->push($listener, $listenerInfo->priority);
        }

        return $eventListenerQueue;
    }
}
