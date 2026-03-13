<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Medicine;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;

class CartController extends Controller
{
    public function index()
    {
        $cart = Auth::user()->cart()->with('items.medicine')->first();

        return view('patient.cart.index', compact('cart'));
    }

    public function addToCart(AddToCartRequest $request)
    {
        $user = $request->user();
        $medicine = Medicine::findOrFail($request->medicine_id);

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $existing = $cart->items()->where('medicine_id', $medicine->id)->first();
        $newQuantity = ($existing ? $existing->quantity : 0) + $request->quantity;

        if ($newQuantity > $medicine->stock_quantity) {
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Requested quantity exceeds available stock.'], 422);
            }

            return back()->withErrors(['quantity' => 'Requested quantity exceeds available stock.']);
        }

        if ($existing) {
            $existing->update(['quantity' => $newQuantity]);
        } else {
            $cart->items()->create([
                'medicine_id' => $medicine->id,
                'quantity' => $request->quantity,
                'price' => $medicine->price,
            ]);
        }

        $cartCount = $cart->items()->count();

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item added to cart.',
                'cart_count' => $cartCount,
            ]);
        }

        return back()->with('success', 'Item added to cart.');
    }

    public function updateQuantity(UpdateCartItemRequest $request)
    {
        $cartItem = CartItem::findOrFail($request->cart_item_id);
        $cartItem->update(['quantity' => $request->quantity]);

        // Prepare response data for AJAX
        $cart = $request->user()->cart()->with('items')->first();
        $itemSubtotal = (float) $cartItem->quantity * (float) $cartItem->price;
        $cartTotal = $cart ? $cart->items->sum(function ($i) { return $i->quantity * (float) $i->price; }) : 0;
        $cartCount = $cart ? $cart->items()->count() : 0;

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated.',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'item_id' => $cartItem->id,
                'item_subtotal' => $itemSubtotal,
            ]);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function removeItem(Request $request)
    {
        $request->validate(['cart_item_id' => ['required', 'integer', 'exists:cart_items,id']]);

        $item = CartItem::findOrFail($request->cart_item_id);
        if ($item->cart->user_id !== $request->user()->id) {
            abort(403);
        }

        $item->delete();

        // Prepare response data for AJAX
        $cart = $request->user()->cart()->with('items')->first();
        $cartTotal = $cart ? $cart->items->sum(function ($i) { return $i->quantity * (float) $i->price; }) : 0;
        $cartCount = $cart ? $cart->items()->count() : 0;

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
                'removed_item_id' => $request->cart_item_id,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    public function clearCart(Request $request)
    {
        $cart = $request->user()->cart;
        if ($cart) {
            $cart->items()->delete();
        }

        // Prepare response data for AJAX
        $cartTotal = 0;
        $cartCount = $cart ? $cart->items()->count() : 0;

        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared.',
                'cart_count' => $cartCount,
                'cart_total' => $cartTotal,
            ]);
        }

        return back()->with('success', 'Cart cleared.');
    }
}
