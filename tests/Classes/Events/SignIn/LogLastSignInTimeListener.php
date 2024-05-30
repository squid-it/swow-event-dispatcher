<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\SignIn;

use SquidIT\Event\Event\EventInterface;
use SquidIT\Event\Listener\ListenerInterface;

class LogLastSignInTimeListener implements ListenerInterface
{
    public function __invoke(UserSignInEvent|EventInterface $event): void
    {
        // Code to register last sign in
    }
}
