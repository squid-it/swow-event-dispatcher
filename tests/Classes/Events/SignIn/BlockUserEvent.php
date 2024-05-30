<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\SignIn;

use DateTimeImmutable;
use Psr\EventDispatcher\StoppableEventInterface;
use SquidIT\Event\Event\EventInterface;

class BlockUserEvent implements EventInterface, StoppableEventInterface
{
    public function __construct(
        public string $username,
        public DateTimeImmutable $signInTime = new DateTimeImmutable(),
    ) {}

    public function isPropagationStopped(): bool
    {
        return true;
    }
}
