<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    public function initializePayment(array $data): array;

    public function verifyPayment(string $reference): bool;
}