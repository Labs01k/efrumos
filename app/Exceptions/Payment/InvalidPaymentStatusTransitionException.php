<?php

namespace App\Exceptions\Payment;

use App\Enums\PaymentStatus;
use RuntimeException;

class InvalidPaymentStatusTransitionException extends RuntimeException
{
    public function __construct(int $ordersId, PaymentStatus $from, PaymentStatus $to)
    {
        parent::__construct(
            "Order #{$ordersId}: payment status transition {$from->value} → {$to->value} is not allowed."
        );
    }
}
