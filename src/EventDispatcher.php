<?php

declare(strict_types=1);

namespace SquidIT\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Log\LoggerInterface;
use SquidIT\Event\Event\EventInterface;
use SquidIT\Event\Listener\ListenerInterface;
use Throwable;

use function get_class;
use function sprintf;

readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $listenerProvider,
        private ?LoggerInterface $logger = null,
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
        /** @var iterable<ListenerInterface> $listeners */
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }

            try {
                $listener($event);
            } catch (Throwable $e) {
                $this->logger?->warning(
                    sprintf(
                        'Something went wrong handling event: "%s" for listener: "%s" error: %s',
                        get_class($event),
                        get_class($listener),
                        $e->getMessage()
                    )
                );

                throw $e;
            }
        }

        return $event;
    }
}
