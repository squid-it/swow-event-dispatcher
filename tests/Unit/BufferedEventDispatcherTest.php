<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Unit;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use SquidIT\Container\Mason\League\LeagueContainerMason;
use SquidIT\Event\BufferedEventDispatcher;
use SquidIT\Event\EventDispatcher;
use SquidIT\Event\ListenerProvider;
use SquidIT\Tests\Event\Classes\Events\Settings\SettingAddedEvent;
use SquidIT\Tests\Event\Classes\Events\Settings\SettingAddedListener;
use SquidIT\Tests\Event\Classes\Events\Settings\SettingsProcessedListener;
use SquidIT\Tests\Event\Classes\Events\Settings\SettingsSavedEvent;
use Throwable;

class BufferedEventDispatcherTest extends TestCase
{
    /** @var ListenerProvider */
    private ListenerProviderInterface $listenerProvider;

    private BufferedEventDispatcher $bufferedEventDispatcher;

    /**
     * @throws Throwable
     */
    public function testEventIsBufferedSuccessfully(): void
    {
        $this->listenerProvider->addEventListener(
            SettingAddedEvent::class,
            SettingAddedListener::class
        );

        $settingAddedEvent = $this->bufferedEventDispatcher->dispatch(new SettingAddedEvent());

        self::assertNull($settingAddedEvent->dateProcessed);
    }

    /**
     * @throws Throwable
     */
    public function testBufferedEventsAreSuccessfullyFlushed(): void
    {
        $this->listenerProvider->addEventListener(
            SettingAddedEvent::class,
            SettingAddedListener::class
        );

        $this->listenerProvider->addEventListener(
            SettingsSavedEvent::class,
            SettingsProcessedListener::class
        );

        $this->bufferedEventDispatcher->dispatch(new SettingAddedEvent());
        $this->bufferedEventDispatcher->dispatch(new SettingsSavedEvent());

        $handledEvents = $this->bufferedEventDispatcher->flushAllEvents();
        self::assertCount(2, $handledEvents);

        foreach ($handledEvents as $events) {
            /** @var SettingAddedEvent|SettingsSavedEvent $event */
            foreach ($events as $event) {
                self::assertNotNull($event->dateProcessed);
            }
        }
    }

    /**
     * @throws Throwable
     */
    public function testOnlySpecifiedEventIsFlushed(): void
    {
        $this->listenerProvider->addEventListener(
            SettingAddedEvent::class,
            SettingAddedListener::class
        );

        $this->listenerProvider->addEventListener(
            SettingsSavedEvent::class,
            SettingsProcessedListener::class
        );

        $this->bufferedEventDispatcher->dispatch(new SettingAddedEvent());
        $this->bufferedEventDispatcher->dispatch(new SettingAddedEvent());
        $this->bufferedEventDispatcher->dispatch(new SettingAddedEvent());
        $this->bufferedEventDispatcher->dispatch(new SettingAddedEvent());
        $this->bufferedEventDispatcher->dispatch(new SettingsSavedEvent());

        $handledEvents1stTime          = $this->bufferedEventDispatcher->flushAllEventsFor(SettingAddedEvent::class);
        $handledEvents2ndTime          = $this->bufferedEventDispatcher->flushAllEventsFor(SettingAddedEvent::class);
        $handledEventsRemaining1stTime = $this->bufferedEventDispatcher->flushAllEvents();
        $handledEventsRemaining2ndTime = $this->bufferedEventDispatcher->flushAllEvents();

        self::assertCount(4, $handledEvents1stTime);
        self::assertCount(0, $handledEvents2ndTime);
        self::assertCount(1, $handledEventsRemaining1stTime);
        self::assertCount(0, $handledEventsRemaining2ndTime);
    }

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        $this->listenerProvider        = new ListenerProvider(new LeagueContainerMason([]));
        $eventDispatcher               = new EventDispatcher($this->listenerProvider);
        $this->bufferedEventDispatcher = new BufferedEventDispatcher($eventDispatcher);
    }
}
