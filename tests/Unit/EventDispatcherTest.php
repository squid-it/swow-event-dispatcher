<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Unit;

use Ds\PriorityQueue;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use RuntimeException;
use SquidIT\Event\EventDispatcher;
use SquidIT\Event\Listener\ListenerInterface;
use SquidIT\Tests\Event\Classes\Events\SignIn\BlockUserEvent;
use SquidIT\Tests\Event\Classes\Events\SignIn\UserSignInEvent;
use Throwable;

class EventDispatcherTest extends TestCase
{
    private ListenerProviderInterface&MockObject $listenerProvider;

    /**
     * @throws Throwable
     */
    public function testEventDispatcherSuccessfullyCallsAllListeners(): void
    {
        $user            = 'Timmy';
        $event           = new UserSignInEvent($user);
        $eventDispatcher = new EventDispatcher($this->listenerProvider);

        $eventListener1 = $this->createMock(ListenerInterface::class);
        $eventListener2 = $this->createMock(ListenerInterface::class);

        $eventListener1->expects(self::once())
            ->method('__invoke')
            ->with($event);

        $eventListener2->expects(self::once())
            ->method('__invoke')
            ->with($event);

        $priorityQueue = new PriorityQueue();
        $priorityQueue->push($eventListener1, 1);
        $priorityQueue->push($eventListener2, 2);

        $this->listenerProvider->expects(self::once())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn($priorityQueue);

        $dispatchedEvent = $eventDispatcher->dispatch($event);
        self::assertSame($event, $dispatchedEvent);
    }

    /**
     * @throws Throwable
     */
    public function testEventDispatcherSuccessfullyStopsOnStoppableEvent(): void
    {
        $user            = 'Timmy';
        $event           = new BlockUserEvent($user);
        $eventDispatcher = new EventDispatcher($this->listenerProvider);

        $eventListener = $this->createMock(ListenerInterface::class);

        $eventListener->expects(self::never())
            ->method('__invoke')
            ->with($event);

        $priorityQueue = new PriorityQueue();
        $priorityQueue->push($eventListener, 1);

        $this->listenerProvider->expects(self::once())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn($priorityQueue->toArray()); // transforming to array because of segfault

        $eventDispatcher->dispatch($event);
    }

    /**
     * @throws Throwable
     */
    public function testEventDispatcherReThrowsExceptionOnListenerException(): void
    {
        $user            = 'Timmy';
        $event           = new UserSignInEvent($user);
        $eventDispatcher = new EventDispatcher($this->listenerProvider);
        $exception       = new RuntimeException('test error');

        $eventListener = $this->createMock(ListenerInterface::class);
        $eventListener->expects(self::once())
            ->method('__invoke')
            ->with($event)
            ->willThrowException($exception);

        $this->listenerProvider->expects(self::once())
            ->method('getListenersForEvent')
            ->with($event)
            ->willReturn([$eventListener]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('test error');

        $eventDispatcher->dispatch($event);
    }

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        $this->listenerProvider = $this->createMock(ListenerProviderInterface::class);
    }
}
