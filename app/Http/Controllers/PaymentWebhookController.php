<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Payments\PaystackWebhookHandler;
use App\Services\Payments\FlutterwaveWebhookHandler;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, $provider)
    {
        $handlers = [
            'paystack' => PaystackWebhookHandler::class,
            'flutterwave' => FlutterwaveWebhookHandler::class,
            // add more like 'stripe' => StripeWebhookHandler::class
        ];

        if (!isset($handlers[$provider])) {
            return response()->json(['error' => 'Unsupported payment provider'], 400);
        }

        $handlerClass = $handlers[$provider];
        $handler = new $handlerClass();

        return $handler->handle($request);
    }
}

