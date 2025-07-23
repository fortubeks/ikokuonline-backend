<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class FlutterwavePaymentService implements PaymentGatewayInterface
{
    protected $baseUrl = 'https://api.flutterwave.com/v3';
    protected $secret;

    public function __construct()
    {
        $this->secret = config('services.flutterwave.secret');
    }

    public function initializePayment(array $data): array
    {
        $response = Http::withToken($this->secret)
            ->post("{$this->baseUrl}/payments", [
                'tx_ref'       => $data['reference'],
                'amount'       => $data['amount'],
                'currency'     => 'NGN',
                'redirect_url' => $data['callback_url'],
                'customer'     => [
                    'email' => $data['email'],
                    'name'  => $data['name'] ?? 'Customer'
                ],
            ]);

        return $response->json();
    }

    public function verifyPayment(string $reference): bool
    {
        $response = Http::withToken($this->secret)
            ->get("{$this->baseUrl}/transactions/verify_by_reference?tx_ref={$reference}");

        return isset($response['status']) && $response['status'] === 'success';
    }
}
