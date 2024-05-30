<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use SquidIT\Container\Mason\League\LeagueContainerMason;
use SquidIT\Event\Exceptions\ListenersAlreadyRegisteredException;
use SquidIT\Event\Exceptions\NoListenersFoundException;
use SquidIT\Event\Listener\CoroutineListenerInterface;
use SquidIT\Event\ListenerProvider;
use SquidIT\Tests\Event\Classes\Events\SignIn\CheckNewSignInLocationListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\LogFirstSignInListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\LogLastSignInTimeListener;
use SquidIT\Tests\Event\Classes\Events\SignIn\UserSignInEvent;
use Throwable;

class ListenerProviderTest extends TestCase
{
    private ListenerProvider $listenerProvider;

    /**
     * @throws Throwable
     */
    public function testAddListenerThrowsListenersAlreadyRegisteredExceptionOnDuplicate(): void
    {
        $eventName = UserSignInEvent::class;
        $listener  = LogLastSignInTimeListener::class;

        $this->expectException(ListenersAlreadyRegisteredException::class);
        $this->expectExceptionMessage(
            sprintf('Listener "%s" is already registered for event: "%s"', $listener, $eventName)
        );

        $this->listenerProvider->addEventListener($eventName, $listener);
        $this->listenerProvider->addEventListener($eventName, $listener);
    }

    /**
     * @throws Throwable
     */
    public function testGetListenersReturnsSuppliedNonCoroutineListeners(): void
    {
        $this->listenerProvider->addEventListener(
            UserSignInEvent::class,
            LogLastSignInTimeListener::class
        );

        $event         = new UserSignInEvent('test.user');
        $priorityQueue = $this->listenerProvider->getListenersForEvent($event);

        self::assertCount(1, $priorityQueue);

        $listener = $priorityQueue->pop();
        self::assertInstanceOf(LogLastSignInTimeListener::class, $listener);
        self::assertNotInstanceOf(CoroutineListenerInterface::class, $listener);
    }

    /**
     * @throws Throwable
     */
    public function testGetListenersReturnsSuppliedCoroutineListeners(): void
    {
        $this->listenerProvider->addEventListener(
            UserSignInEvent::class,
            CheckNewSignInLocationListener::class
        );

        $event         = new UserSignInEvent('test.user');
        $priorityQueue = $this->listenerProvider->getListenersForEvent($event);

        self::assertCount(1, $priorityQueue);

        $listener = $priorityQueue->pop();
        self::assertInstanceOf(CheckNewSignInLocationListener::class, $listener);
        self::assertInstanceOf(CoroutineListenerInterface::class, $listener);
    }

    /**
     * @throws Throwable
     */
    public function testGetListenersThrowsNoListenersFoundForEventExceptionWhenNoListenersArePresentForEvent(): void
    {
        $this->expectException(NoListenersFoundException::class);
        $this->expectExceptionMessage(
            sprintf('No event listeners found for event: %s.', UserSignInEvent::class)
        );

        $this->listenerProvider->getListenersForEvent(new UserSignInEvent('test.user'));
    }

    /**
     * @throws Throwable
     */
    public function testGetListenersReturnsListenersInTheProperQueueOrder(): void
    {
        $eventList = [
            [
                'listener' => LogLastSignInTimeListener::class,
                'priority' => 10,
            ],
            [
                'listener' => CheckNewSignInLocationListener::class,
                'priority' => 30,
            ],
            [
                'listener' => LogFirstSignInListener::class,
                'priority' => 20,
            ],
        ];

        foreach ($eventList as $eventInfo) {
            $this->listenerProvider->addEventListener(
                UserSignInEvent::class,
                $eventInfo['listener'],
                (int) $eventInfo['priority']
            );
        }

        $event         = new UserSignInEvent('test.user');
        $priorityQueue = $this->listenerProvider->getListenersForEvent($event);

        self::assertCount(3, $priorityQueue);
        $priorityList = $priorityQueue->toArray();

        self::assertInstanceOf(CheckNewSignInLocationListener::class, $priorityList[0]);
        self::assertInstanceOf(LogFirstSignInListener::class, $priorityList[1]);
        self::assertInstanceOf(LogLastSignInTimeListener::class, $priorityList[2]);
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $container              = new LeagueContainerMason([]);
        $this->listenerProvider = new ListenerProvider($container);
    }
}
