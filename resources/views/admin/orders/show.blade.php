@extends('admin.layout')

@section('title', 'Order Details')
@section('page-title', 'Order #'.$order->id)

@section('content')
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card admin-panel h-100">
            <div class="card-body p-4 p-xl-5">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h5 admin-section-title mb-1">Order Items</h2>
                <p class="admin-muted mb-0">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <span class="admin-chip {{ $order->status }}">{{ ucfirst($order->status) }}</span>
        </div>

        <div class="table-responsive">
            <table class="table admin-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->medicine?->name ?? 'Deleted medicine' }}</td>
                            <td>Rs. {{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>Rs. {{ number_format($item->quantity * $item->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="d-grid gap-4">
        <div class="card admin-panel">
            <div class="card-body p-4">
            <h2 class="h5 admin-section-title mb-3">Patient Info</h2>
            <div class="small admin-muted mb-1">Name</div>
            <div class="fw-semibold mb-3">{{ $order->user?->name }}</div>
            <div class="small admin-muted mb-1">Email</div>
            <div class="fw-semibold mb-3">{{ $order->user?->email }}</div>
            <div class="small admin-muted mb-1">Role</div>
            <div><span class="admin-chip {{ $order->user?->role?->name === 'admin' ? 'admin-role' : 'patient-role' }}">{{ ucfirst($order->user?->role?->name ?? 'N/A') }}</span></div>
            </div>
        </div>

        <div class="card admin-panel">
            <div class="card-body p-4">
            <h2 class="h5 admin-section-title mb-3">Payment Summary</h2>
            <div class="small admin-muted mb-1">Method</div>
            <div class="fw-semibold mb-3">{{ strtoupper($order->payment_method ?: 'N/A') }}</div>
            <div class="small admin-muted mb-1">Payment ID</div>
            <div class="fw-semibold mb-3">{{ $order->payment_id ?: 'N/A' }}</div>
            <div class="small admin-muted mb-1">Amount</div>
            <div class="fw-semibold">Rs. {{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="card admin-panel">
            <div class="card-body p-4">
            <h2 class="h5 admin-section-title mb-3">Update Status</h2>
            <form action="{{ route('admin.orders.update', $order) }}" method="POST" class="d-grid gap-3">
                @csrf
                @method('PUT')
                <div>
                    <label for="status" class="form-label fw-semibold">Order Status</label>
                    <select id="status" name="status" class="form-select admin-form-select">
                        @foreach (['pending', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success rounded-pill">Update Order</button>
            </form>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
