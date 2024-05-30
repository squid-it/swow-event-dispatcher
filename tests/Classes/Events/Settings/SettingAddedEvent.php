<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Settings;

use DateTimeImmutable;
use SquidIT\Event\Event\EventInterface;

class SettingAddedEvent implements EventInterface
{
    public function __construct(
        public ?DateTimeImmutable $dateSaved = null,
        public ?DateTimeImmutable $dateProcessed = null,
    ) {}
}
