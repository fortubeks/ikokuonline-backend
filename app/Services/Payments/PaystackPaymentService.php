<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class PaystackPaymentService implements PaymentGatewayInterface
{
    protected $baseUrl = 'https://api.paystack.co';
    protected $secret;

    public function __construct()
    {
        $this->secret = config('services.paystack.secret');
    }

    public function initializePayment(array $data): array
    {
        $response = Http::withToken($this->secret)
            ->post("{$this->baseUrl}/transaction/initialize", [
                'amount'       => $data['amount'] * 100, // Paystack uses kobo
                'email'        => $data['email'],
                'reference'    => $data['reference'],
                'callback_url' => $data['callback_url'],
            ]);

        return $response->json();
    }

    public function verifyPayment(string $reference): bool
    {
        $response = Http::withToken($this->secret)
            ->get("{$this->baseUrl}/transaction/verify/{$reference}");

        return $response['data']['status'] === 'success';
    }
}
