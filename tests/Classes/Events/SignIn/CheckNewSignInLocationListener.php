<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\SignIn;

use SquidIT\Event\Event\EventInterface;
use SquidIT\Event\Listener\AbstractCoroutineListener;

class CheckNewSignInLocationListener extends AbstractCoroutineListener
{
    public function coroutineExecute(UserSignInEvent|EventInterface $event): void
    {
        // Application Code running inside a coroutine to check if a user signed in from a new location.
    }
}
