<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Razorpay\Api\Api;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Str;

class RazorpayController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Create a Razorpay order using the authenticated user's cart total.
     * Returns order id and other info required by the frontend.
     */
    public function createOrder(Request $request)
    {
        $user = $request->user();

        // Load user's cart and compute total server-side to avoid manipulation
        $cart = Cart::where('user_id', $user->id)->with('items')->first();
        if (! $cart || $cart->items->isEmpty()) {
            return Response::json(['message' => 'Cart is empty'], 422);
        }

        $amountInRupees = (float) $cart->total;
        $amountInPaisa = (int) round($amountInRupees * 100);

        $key = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        if (! $key || ! $secret) {
            return Response::json(['message' => 'Payment gateway not configured'], 500);
        }

        try {
            $api = new Api($key, $secret);

            $orderData = [
                'receipt'         => 'rcpt_' . Str::random(8),
                'amount'          => $amountInPaisa,
                'currency'        => 'INR',
                'payment_capture' => 1, // auto-capture
            ];

            $razorpayOrder = $api->order->create($orderData);

            return Response::json([
                'id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'key' => $key,
            ]);
        } catch (\Exception $e) {
            Log::error('Razorpay createOrder error: ' . $e->getMessage());
            return Response::json(['message' => 'Could not create payment order'], 500);
        }
    }

    /**
     * Verify payment signature returned by Razorpay and store successful payment to orders table.
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $paymentId = $request->input('razorpay_payment_id');
        $orderId = $request->input('razorpay_order_id');
        $signature = $request->input('razorpay_signature');

        $key = env('RAZORPAY_KEY');
        $secret = env('RAZORPAY_SECRET');

        if (! $key || ! $secret) {
            return Response::json(['message' => 'Payment gateway not configured'], 500);
        }

        try {
            // verify signature using HMAC SHA256
            $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);

            if (! hash_equals($generatedSignature, $signature)) {
                Log::warning('Razorpay signature mismatch', ['generated' => $generatedSignature, 'received' => $signature]);
                return Response::json(['message' => 'Invalid payment signature'], 422);
            }

            // Optionally you may fetch payment details from Razorpay to double-check amount/status
            $api = new Api($key, $secret);
            $payment = $api->payment->fetch($paymentId);

            // Ensure payment captured and status is authorized or captured
            if (! in_array(strtolower($payment['status']), ['captured', 'authorized'])) {
                Log::warning('Razorpay payment not captured', ['status' => $payment['status']]);
                return Response::json(['message' => 'Payment not successful'], 422);
            }

            // Store successful payment into orders table
            $user = $request->user();

            $amountInRupees = ($payment['amount'] ?? 0) / 100; // payment amount is in paisa

            $order = Order::create([
                'user_id' => $user->id,
                'payment_id' => $paymentId,
                'total_amount' => $amountInRupees,
                'status' => 'paid',
                'payment_method' => 'razorpay',
            ]);

            return Response::json(['message' => 'Payment verified', 'order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Razorpay verifyPayment error: ' . $e->getMessage());
            return Response::json(['message' => 'Verification failed'], 500);
        }
    }
}
