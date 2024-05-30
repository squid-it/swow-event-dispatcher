<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Payment;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use SquidIT\Event\Event\EventInterface;

abstract class PaymentEvent implements PaymentEventInterface, EventInterface
{
    /**
     * @throws Exception
     */
    public function getRecordedTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
