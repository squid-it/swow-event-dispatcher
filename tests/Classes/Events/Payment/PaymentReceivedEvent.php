<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Payment;

class PaymentReceivedEvent extends PaymentEvent
{
    public ?string $paymentNode = null;

    public function getPaymentType(): PaymentType
    {
        return PaymentType::RECEIVED;
    }
}
