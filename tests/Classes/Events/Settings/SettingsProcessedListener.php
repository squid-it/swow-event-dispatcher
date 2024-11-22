<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Settings;

use DateTimeImmutable;
use DateTimeZone;
use SquidIT\Event\Event\EventInterface;
use SquidIT\Event\Listener\ListenerInterface;

class SettingsProcessedListener implements ListenerInterface
{
    public function __invoke(SettingsSavedEvent|EventInterface $event): void
    {
        /** @var SettingsSavedEvent $event */
        $event->dateProcessed = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
