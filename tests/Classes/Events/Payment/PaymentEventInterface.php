<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Payment;

use DateTimeImmutable;

interface PaymentEventInterface
{
    public function getPaymentType(): PaymentType;

    public function getRecordedTime(): DateTimeImmutable;
}
