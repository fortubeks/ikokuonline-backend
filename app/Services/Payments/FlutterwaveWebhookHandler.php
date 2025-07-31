<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;
use App\Models\Order;

class FlutterwaveWebhookHandler
{
    public function handle(Request $request)
    {
        $data = $request->input('data');

        if ($data['status'] === 'successful' && $data['chargecode'] === '00') {
            $orderId = $data['meta']['order_id'] ?? null;
            if ($orderId) {
                Order::where('id', $orderId)->update(['status' => 'paid']);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
