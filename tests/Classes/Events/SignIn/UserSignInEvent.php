<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\SignIn;

use DateTimeImmutable;
use SquidIT\Event\Event\EventInterface;

readonly class UserSignInEvent implements EventInterface
{
    public function __construct(
        public string $username,
        public DateTimeImmutable $signInTime = new DateTimeImmutable(),
    ) {}
}
