<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Events\LoginEvent;

class MergeGuestCartAfterLogin
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LoginEvent $event): void
    {
        //
        $guestId = request()->cookie('guest_id');
        if (!$guestId) return;

        $guestCart = Cart::where('guest_id', $guestId)->first();
        if (!$guestCart) return;

        $userCart = Cart::firstOrCreate(['user_id' => $event->user->id]);

        foreach ($guestCart->items as $item) {
            $userCart->items()->updateOrCreate(
                ['product_id' => $item->product_id],
                ['quantity' => DB::raw("quantity + {$item->quantity}")]
            );
        }

        $guestCart->delete();
    }
}
