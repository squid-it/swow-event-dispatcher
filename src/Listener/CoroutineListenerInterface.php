<?php

declare(strict_types=1);

namespace SquidIT\Event\Listener;

use SquidIT\Event\Event\EventInterface;

interface CoroutineListenerInterface extends ListenerInterface
{
    public function coroutineExecute(EventInterface $event): void;
}
