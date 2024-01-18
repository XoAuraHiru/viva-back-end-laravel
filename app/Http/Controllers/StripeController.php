<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret')); // Set your secret key

            // Create a PaymentIntent on Stripe's servers
            $intent = PaymentIntent::create([
                'amount' => $request->input('amount'),
                'currency' => 'usd', // Adjust currency as needed
                'payment_method_types' => ['card'],
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
