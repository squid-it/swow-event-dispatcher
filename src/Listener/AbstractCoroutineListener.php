<?php

declare(strict_types=1);

namespace SquidIT\Event\Listener;

use SquidIT\Event\Event\EventInterface;
use Swow\Coroutine;

abstract class AbstractCoroutineListener implements CoroutineListenerInterface
{
    public function __invoke(EventInterface $event): void
    {
        Coroutine::run($this->coroutineExecute(...), $event);
    }

    /**
     * Handle event inside this method
     */
    abstract public function coroutineExecute(EventInterface $event): void;
}
