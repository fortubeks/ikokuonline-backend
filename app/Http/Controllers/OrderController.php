<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    use ApiResponse;

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'delivery_address'   => 'required|string',
            'delivery_area_id'   => 'required|exists:delivery_areas,id',
            'phone'              => 'required|string',
            'email'              => 'required|email',
            'country_id'         => 'nullable|exists:countries,id',
            'state_id'           => 'nullable|exists:states,id',
            'payment_method'     => 'required|in:paystack,flutterwave', // Add more options as needed
        ]);

        [$cart, $guestId] = $this->getCart($request);

        if ($cart->items->isEmpty()) {
            return $this->error('Cart is empty', 400);
        }

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            foreach ($cart->items as $item) {
                $totalAmount += $item->quantity * $item->product->price;
            }

            $deliveryFee = 1000; // You can later fetch this dynamically

            $order = Order::create([
                'user_id'         => auth()->id(),
                'email'           => $data['email'],
                'phone'           => $data['phone'],
                'delivery_address'=> $data['delivery_address'],
                'country_id'      => $data['country_id'],
                'state_id'        => $data['state_id'],
                'delivery_area_id'=> $data['delivery_area_id'],
                'delivery_fee'    => $deliveryFee,
                'total_amount'    => $totalAmount + $deliveryFee,
                'status'          => 'pending',
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);
            }

            $cart->items()->delete(); // Clear cart

            DB::commit();

            // Redirect to payment
            $paymentData = $this->initiatePayment($order, $data['payment_method']);

            return $this->success([
                'message' => 'Order created. Redirect to payment.',
                'order_id' => $order->id,
                'payment' => $paymentData,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to process checkout: ' . $e->getMessage(), 500);
        }
    }

    protected function initiatePayment(Order $order, $method)
    {
        $paymentRef = 'ORD-' . $order->id . '-' . strtoupper(Str::random(6));

        if ($method === 'paystack') {
            return [
                'provider' => 'paystack',
                'url' => route('paystack.init', ['order' => $order->id, 'ref' => $paymentRef])
            ];
        }

        if ($method === 'flutterwave') {
            return [
                'provider' => 'flutterwave',
                'url' => route('flutterwave.init', ['order' => $order->id, 'ref' => $paymentRef])
            ];
        }

        throw new \Exception("Unsupported payment method");
    }

    private function getCart(Request $request)
    {
        if (auth()->check()) {
            $cart = Cart::with('items.product')->where('user_id', auth()->id())->first();
            return [$cart, null];
        }

        $guestId = $request->cookie('guest_id');
        $cart = Cart::with('items.product')->where('guest_id', $guestId)->first();
        return [$cart, $guestId];
    }

    public function verifyPaystack(Request $request)
    {
        // Verify Paystack Signature
        $secret = config('services.paystack.secret');
        $signature = $request->header('x-paystack-signature');

        $payload = $request->getContent();

        if (hash_hmac('sha512', $payload, $secret) !== $signature) {
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

    public function verifyFlutterwave(Request $request)
    {
        // Optionally verify Flutterwave signature if configured
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
