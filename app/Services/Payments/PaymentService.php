<?php

namespace App\Services\Payments;

class PaymentService
{
    public static function make(string $gateway): PaymentGatewayInterface
    {
        return match (strtolower($gateway)) {
            'flutterwave' => new FlutterwavePaymentService(),
            'paystack'    => new PaystackPaymentService(),
            default       => throw new \Exception("Unsupported payment gateway: {$gateway}"),
        };
    }
}
