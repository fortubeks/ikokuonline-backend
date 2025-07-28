<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class UserOrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->with('items.product')
            ->get();

        return $this->success($orders, 'Orders retrieved successfully');
    }

    public function show(Request $request, Order $order)
    {
        //$this->authorize('view', $order); // Ensure order belongs to user
        if ($request->user()->cannot('show', $order)) {
            return $this->error('Unauthorized access', [], 403);
        }

        return $this->success($order->load('items.product'), 'Order retrieved successfully');
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        //$this->authorize('update', $order);
        if ($request->user()->cannot('update', $order)) {
            return $this->error('Unauthorized access', [], 403);
        }

        $order->update($request->validated());

        return $this->success($order, 'Order updated successfully');
    }

    public function destroy(Request $request, Order $order)
    {
        //$this->authorize('delete', $order);
        if ($request->user()->cannot('delete', $order)) {
            return $this->error('Unauthorized access', [], 403);
        }

        $order->delete();

        return $this->success(null, 'Order deleted successfully');
    }
}
