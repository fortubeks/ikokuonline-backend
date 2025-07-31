<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\Payments\PaymentService;
use App\Traits\ApiResponse;

class PaymentController extends Controller
{
    use ApiResponse;

    public function initialize(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'gateway' => 'required|in:paystack,flutterwave',
            'callback_url' => 'required|url',
        ]);

        $order = Order::findOrFail($data['order_id']);

        $gateway = PaymentService::make($data['gateway']);

        $payment = $gateway->initialize([
            'amount' => $order->total_amount + $order->delivery_fee,
            'email' => $order->email,
            'tx_ref' => 'ORDER-' . $order->id . '-' . now()->timestamp,
            'callback_url' => $data['callback_url'],
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        return $this->success($payment, 'Payment initialized');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'gateway' => 'required|in:paystack,flutterwave',
            'transaction_id' => 'required|string',
        ]);

        $gateway = PaymentService::make($data['gateway']);

        $verification = $gateway->verify($data['transaction_id']);

        // You can update your order based on verification data
        // e.g. $order = Order::find($verification['data']['metadata']['order_id']);

        return $this->success($verification, 'Payment verification complete');
    }
}
