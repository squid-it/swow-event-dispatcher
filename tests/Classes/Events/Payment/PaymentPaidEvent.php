<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Payment;

class PaymentPaidEvent extends PaymentEvent
{
    public function getPaymentType(): PaymentType
    {
        return PaymentType::PAID;
    }
}
