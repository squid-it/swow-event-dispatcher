<?php

declare(strict_types=1);

namespace SquidIT\Tests\Event\Classes\Events\Payment;

enum PaymentType: string
{
    case PAID     = 'paid';
    case RECEIVED = 'received';
    case RESERVED = 'reserved';
}
