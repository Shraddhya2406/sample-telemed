<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Medicine;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\Error as RazorpayError;
use Illuminate\Support\Str;

class RazorpayController extends Controller
{
    /**
     * Create a Razorpay order using the authenticated user's cart total.
     * Returns order id and other info required by the frontend.
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'integer', 'min:100'],
            'currency' => ['nullable', 'string', 'size:3'],
            'receipt' => ['nullable', 'string', 'max:40'],
        ]);

        if ($validator->fails()) {
            return Response::json([
                'message' => 'Invalid order request',
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = $request->user();

        // Load user's cart and compute total server-side to avoid manipulation
        $cart = Cart::where('user_id', $user->id)->with('items')->first();
        if (! $cart || $cart->items->isEmpty()) {
            return Response::json(['message' => 'Cart is empty'], 400);
        }

        $amountInRupees = (float) $cart->total;
        $amountInPaisa = (int) round($amountInRupees * 100);
        $requestedAmount = (int) $request->input('amount');

        if ($amountInPaisa < 100 || $requestedAmount !== $amountInPaisa) {
            return Response::json(['message' => 'Invalid payment amount'], 400);
        }

        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');

        if (! $key || ! $secret) {
            return Response::json(['message' => 'Payment gateway not configured'], 500);
        }

        try {
            $this->disableProxyForRazorpay();

            $api = new Api($key, $secret);

            $orderData = [
                'receipt'         => $request->input('receipt', 'rcpt_' . Str::random(8)),
                'amount'          => $amountInPaisa,
                'currency'        => strtoupper($request->input('currency', 'INR')),
                'payment_capture' => 1, // auto-capture
            ];

            $razorpayOrder = $api->order->create($orderData);

            $request->session()->put('razorpay_orders.' . $razorpayOrder['id'], [
                'amount' => (int) $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
            ]);

            return Response::json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
            ]);
        } catch (RazorpayError $e) {
            Log::error('Razorpay createOrder API error: ' . $e->getMessage());

            if ((int) $e->getHttpStatusCode() === 401) {
                return Response::json(['message' => 'Payment gateway authentication failed'], 401);
            }

            return Response::json(['message' => 'Could not create payment order'], 500);
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
        $validator = Validator::make($request->all(), [
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Response::json([
                'message' => 'Missing payment verification fields',
                'errors' => $validator->errors(),
            ], 400);
        }

        $paymentId = $request->input('razorpay_payment_id');
        $orderId = $request->input('razorpay_order_id');
        $signature = $request->input('razorpay_signature');

        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');

        if (! $key || ! $secret) {
            return Response::json(['message' => 'Payment gateway not configured'], 500);
        }

        try {
            if (Order::where('payment_id', $paymentId)->exists()) {
                $existingOrder = Order::where('payment_id', $paymentId)->first();

                return Response::json([
                    'message' => 'Payment already verified',
                    'order_id' => $existingOrder->id,
                ]);
            }

            $generatedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);

            if (! hash_equals($generatedSignature, $signature)) {
                Log::warning('Razorpay signature mismatch', ['payment_id' => $paymentId, 'order_id' => $orderId]);
                return Response::json(['message' => 'Invalid payment signature'], 400);
            }

            $user = $request->user();
            $cart = Cart::where('user_id', $user->id)->with('items.medicine')->first();

            if (! $cart || $cart->items->isEmpty()) {
                return Response::json(['message' => 'Cart is empty'], 400);
            }

            $amountInPaisa = (int) round((float) $cart->total * 100);
            $razorpayOrder = $request->session()->get('razorpay_orders.' . $orderId);

            if ($razorpayOrder && (int) ($razorpayOrder['amount'] ?? 0) !== $amountInPaisa) {
                Log::warning('Razorpay amount mismatch', [
                    'payment_id' => $paymentId,
                    'razorpay_order_id' => $orderId,
                    'razorpay_order_amount' => $razorpayOrder['amount'] ?? null,
                    'cart_amount' => $amountInPaisa,
                ]);

                return Response::json(['message' => 'Payment amount mismatch'], 400);
            }

            $order = null;

            DB::transaction(function () use ($cart, $user, $paymentId, $amountInPaisa, &$order) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'payment_id' => $paymentId,
                    'total_amount' => $amountInPaisa / 100,
                    'amount' => $amountInPaisa / 100,
                    'status' => 'paid',
                    'payment_method' => 'razorpay',
                ]);

                foreach ($cart->items as $item) {
                    $medicine = Medicine::lockForUpdate()->find($item->medicine_id);

                    if (! $medicine || ! $medicine->is_active) {
                        throw new \RuntimeException('Medicine is no longer available.');
                    }

                    if ($item->quantity > $medicine->stock_quantity) {
                        throw new \RuntimeException('Insufficient stock for ' . $medicine->name . '.');
                    }

                    $order->items()->create([
                        'medicine_id' => $medicine->id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ]);

                    $medicine->decrement('stock_quantity', $item->quantity);
                }

                $cart->items()->delete();
            });

            $request->session()->forget('razorpay_orders.' . $orderId);

            return Response::json(['message' => 'Payment verified', 'order_id' => $order->id]);
        } catch (RazorpayError $e) {
            Log::error('Razorpay verifyPayment API error: ' . $e->getMessage());

            if ((int) $e->getHttpStatusCode() === 401) {
                return Response::json(['message' => 'Payment gateway authentication failed'], 401);
            }

            return Response::json(['message' => 'Verification failed'], 500);
        } catch (\Exception $e) {
            Log::error('Razorpay verifyPayment error: ' . $e->getMessage());
            return Response::json(['message' => 'Verification failed'], 500);
        }
    }

    private function disableProxyForRazorpay(): void
    {
        foreach (['HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy'] as $name) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }
}
