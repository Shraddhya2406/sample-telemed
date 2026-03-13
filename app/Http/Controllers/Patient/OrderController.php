<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Http\Requests\PlaceOrderRequest;

class OrderController extends Controller
{
    public function checkout()
    {
        $cart = Auth::user()->cart()->with('items.medicine')->first();
        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('patient.cart.index')->withErrors(['cart' => 'Your cart is empty.']);
        }

        return view('patient.orders.checkout', compact('cart'));
    }

    public function placeOrder(PlaceOrderRequest $request)
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.medicine')->first();
        if (! $cart || $cart->items->isEmpty()) {
            return back()->withErrors(['cart' => 'Your cart is empty.']);
        }

        $order = null;

        DB::transaction(function () use ($cart, $user, $request, &$order) {
            $total = 0;
            foreach ($cart->items as $item) {
                $total += $item->quantity * (float) $item->price;
            }

            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $request->input('payment_method'),
            ]);

            foreach ($cart->items as $item) {
                // re-check stock
                $medicine = Medicine::lockForUpdate()->find($item->medicine_id);
                if (! $medicine || ! $medicine->is_active) {
                    throw new \Exception('Medicine not available: ' . $item->medicine_id);
                }

                if ($item->quantity > $medicine->stock_quantity) {
                    throw new \Exception('Insufficient stock for: ' . $medicine->name);
                }

                // create order item
                $order->items()->create([
                    'medicine_id' => $medicine->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]);

                // decrement stock
                $medicine->decrement('stock_quantity', $item->quantity);
            }

            // clear cart
            $cart->items()->delete();
        });

        if (! $order) {
            return back()->withErrors(['order' => 'Failed to create order.']);
        }

        return redirect()->route('patient.orders.show', [$order->id])->with('success', 'Order placed successfully.');
    }

    public function myOrders()
    {
        $orders = Auth::user()->orders()->with('items.medicine')->orderBy('created_at', 'desc')->paginate(12);

        return view('patient.orders.index', compact('orders'));
    }

    public function orderDetails(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load('items.medicine');

        return view('patient.orders.show', compact('order'));
    }
}
