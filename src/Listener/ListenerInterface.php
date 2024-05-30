<?php

declare(strict_types=1);

namespace SquidIT\Event\Listener;

use SquidIT\Event\Event\EventInterface;

interface ListenerInterface
{
    public function __invoke(EventInterface $event): void;
}
