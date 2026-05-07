<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['user:id,name,email,role_id', 'items.medicine:id,name'])
            ->latest()
            ->get();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load([
            'user:id,name,email,role_id',
            'user.role:id,name',
            'items.medicine:id,name',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    public function update(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order->update($request->validated());

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Order status updated successfully.');
    }
}
