<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;
use App\Models\Order;

class PaystackWebhookHandler
{
    public function handle(Request $request)
    {
        $secret = config('services.paystack.secret');
        $signature = $request->header('x-paystack-signature');

        if (hash_hmac('sha512', $request->getContent(), $secret) !== $signature) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $data = $request->input('data');

        if ($data['status'] === 'success') {
            $orderId = $data['metadata']['order_id'] ?? null;
            if ($orderId) {
                Order::where('id', $orderId)->update(['status' => 'paid']);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
