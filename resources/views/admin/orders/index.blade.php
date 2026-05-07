@extends('admin.layout')

@section('title', 'Orders')
@section('page-title', 'Order Management')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
    <div class="mb-6">
        <h2 class="h5 admin-section-title mb-1">All Patient Orders</h2>
        <p class="admin-muted mb-0">Review order values, payment references, and fulfillment progress.</p>
    </div>

    <div class="table-responsive">
        <table class="table admin-table table-striped align-middle mb-0 js-admin-datatable">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Patient</th>
                    <th>Items</th>
                    <th>Payment</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th class="text-end">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="fw-semibold">#{{ $order->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $order->user?->name }}</div>
                            <div class="small admin-muted">{{ $order->user?->email }}</div>
                        </td>
                        <td>{{ $order->items->count() }}</td>
                        <td>
                            <div>{{ strtoupper($order->payment_method ?: 'N/A') }}</div>
                            <div class="small admin-muted">{{ $order->payment_id ?: 'No payment ID' }}</div>
                        </td>
                        <td>Rs. {{ number_format($order->total_amount, 2) }}</td>
                        <td><span class="admin-chip {{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm rounded-pill">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="admin-empty-state">No orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
@endsection
