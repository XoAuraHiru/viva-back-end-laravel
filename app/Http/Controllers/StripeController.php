<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $intent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'order_id' => $request->orderID,
                    'user_id' => $request->userID,
                ],
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

    public function handleWebhook(Request $request)
    {
        try {

            $payload = @file_get_contents('php://input');
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

            try {
                $event = Webhook::constructEvent(
                    $request->all(),
                    $request->getContent(),
                    $sig_header,
                    config('services.stripe.webhook_secret')
                );
            } catch (\Exception $e) {
                return response()->json(['message' => 'Webhook request error' . $e], 400);
            }

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                    // ... handle successful payment intent
                    $order_id = $paymentIntent->metadata->order_id;

                    try {
                        $order = Order::find($order_id);
                        $order->paid_status = 1;
                        $order->paid_at = date('Y-m-d H:i:s');
                        $order->save();
                    } catch (\Exception $e) {
                        return response()->json(['message' => 'Webhook error: Failed to update order' . $e], 500);
                    }
                    break;
                    // ... handle other events as needed
            }

            return response()->json(['message' => 'Webhook received'], 200);
        } catch (\Exception $e) {
            // Log errors and return a generic response
            return response()->json(['message' => 'Webhook error' . $e], 500);
        }
    }
}
