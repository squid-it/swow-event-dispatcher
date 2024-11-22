<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Payment;

use DateTimeImmutable;
use DateTimeZone;
use SquidIT\Event\Event\EventInterface;

abstract class PaymentEvent implements PaymentEventInterface, EventInterface
{
    public function getRecordedTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
