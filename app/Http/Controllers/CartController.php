<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Cart;
use App\Models\CartItem;
use App\Traits\ApiResponse;

class CartController extends Controller
{
    use ApiResponse;

    public function getCart(Request $request)
    {
        [$cart, $guestId, $isNewGuest] = $this->getOrCreateCart($request);

        $cart->load(['items.product' => function ($query) {
            $query->select('id', 'name', 'price'); // tailor as needed
        }]);

        $response = $this->success($cart, 'Cart retrieved successfully');

        // Attach cookie if it's a new guest
        if ($isNewGuest) {
            return $response->withCookie(cookie('guest_id', $guestId, 60 * 24 * 7)); // 7 days
        }

        return $response;
    }

    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ]);

        [$cart, $guestId, $isNewGuest] = $this->getOrCreateCart($request);

        $cart->items()->updateOrCreate(
            ['product_id' => $data['product_id']],
            ['quantity' => DB::raw("quantity + {$data['quantity']}")]
        );

        $response = $this->success('Product added to cart');

        if ($isNewGuest) {
            return $response->withCookie(cookie('guest_id', $guestId, 60 * 24 * 7));
        }

        return $response;
    }

    public function updateQuantity(Request $request, $productId)
    {
        $data = $request->validate([
            'action' => 'required|in:increase,decrease'
        ]);

        [$cart] = $this->getOrCreateCart($request);

        $item = $cart->items()->where('product_id', $productId)->first();

        if (!$item) {
            return $this->error('Item not found in cart', 404);
        }

        if ($data['action'] === 'increase') {
            $item->increment('quantity');
        } elseif ($data['action'] === 'decrease') {
            if ($item->quantity > 1) {
                $item->decrement('quantity');
            } else {
                $item->delete();
            }
        }

        return $this->success(null, 'Cart updated successfully');
    }

    public function removeFromCart(Request $request, $productId)
    {
        [$cart, $guestId, $isNewGuest] = $this->getOrCreateCart($request);

        $deleted = $cart->items()->where('product_id', $productId)->delete();

        if (!$deleted) {
            return $this->error('Item not found in cart', 404);
        }

        $response = $this->success(null, 'Product removed from cart');

        if ($isNewGuest) {
            return $response->withCookie(cookie('guest_id', $guestId, 60 * 24 * 7));
        }

        return $response;
    }
    

    /**
     * Get or create a cart for the user or guest.
     * Returns [Cart $cart, string|null $guestId, bool $isNewGuest]
     */
    private function getOrCreateCart(Request $request): array
    {
        if (auth()->check()) {
            return [Cart::firstOrCreate(['user_id' => auth()->id()]), null, false];
        }

        $guestId = $request->cookie('guest_id');
        $isNewGuest = false;

        if (!$guestId) {
            $guestId = (string) Str::uuid();
            $isNewGuest = true;
        }

        $cart = Cart::firstOrCreate(['guest_id' => $guestId]);

        return [$cart, $guestId, $isNewGuest];
    }
}
