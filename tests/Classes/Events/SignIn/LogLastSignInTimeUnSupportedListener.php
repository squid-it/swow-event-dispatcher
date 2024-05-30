<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\SignIn;

use SquidIT\Event\Event\EventInterface;

class LogLastSignInTimeUnSupportedListener
{
    public function __invoke(UserSignInEvent|EventInterface $event): void
    {
        // Code to register last login
    }
}
