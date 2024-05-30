<?php

declare(strict_types=1);

namespace SquidIT\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use SquidIT\Event\Event\EventInterface;
use Throwable;

class BufferedEventDispatcher implements EventDispatcherInterface
{
    /** @var array<class-string, array<int, EventInterface>> */
    private array $bufferedEvents = [];

    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
    ) {}

    /**
     * @template TEvent of EventInterface
     *
     * @phpstan-param TEvent $event
     *
     * @throws Throwable
     *
     * @phpstan-return TEvent
     */
    public function dispatch(object $event): EventInterface
    {
        $eventClassName = get_class($event);

        $this->bufferedEvents[$eventClassName][] = $event;

        return $event;
    }

    /**
     * @throws Throwable
     *
     * @return array<class-string, array<int, EventInterface>>
     */
    public function flushAllEvents(): iterable
    {
        $flushedEvents = [];

        foreach ($this->bufferedEvents as $eventClassName => $events) {
            foreach ($events as $event) {
                $flushedEvents[$eventClassName][] = $this->eventDispatcher->dispatch($event);
            }
        }

        $this->bufferedEvents = [];

        return $flushedEvents;
    }

    /**
     * @param class-string $eventClassName
     *
     * @throws Throwable
     *
     * @return array<int, EventInterface>
     */
    public function flushAllEventsFor(string $eventClassName): iterable
    {
        if (empty($this->bufferedEvents[$eventClassName])) {
            return [];
        }

        $flushedEvents = [];

        foreach ($this->bufferedEvents[$eventClassName] as $event) {
            $flushedEvents[] = $this->eventDispatcher->dispatch($event);
        }

        $this->bufferedEvents[$eventClassName] = [];

        return $flushedEvents;
    }
}
