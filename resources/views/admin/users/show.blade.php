@extends('admin.layout')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="row g-4">
    <div class="col-xl-5">
        <div class="card admin-panel">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div>
                        <h2 class="h4 mb-1">{{ $user->name }}</h2>
                        <p class="admin-muted mb-0">{{ $user->email }}</p>
                    </div>
                    <span class="admin-chip {{ $user->role?->name === 'admin' ? 'admin-role' : 'patient-role' }}">
                        {{ ucfirst($user->role?->name ?? 'No role') }}
                    </span>
                </div>

                <div class="mb-3">
                    <div class="small admin-muted">User ID</div>
                    <div class="fw-semibold">#{{ $user->id }}</div>
                </div>
                <div class="mb-3">
                    <div class="small admin-muted">Registered On</div>
                    <div class="fw-semibold">{{ $user->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</div>
                </div>
                <div class="mb-4">
                    <div class="small admin-muted">Total Orders</div>
                    <div class="fw-semibold">{{ $user->orders->count() }}</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary rounded-pill px-4">Edit Role</a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to Users</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="card admin-panel">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h5 admin-section-title mb-3">Order History</h3>

                @if ($user->orders->isEmpty())
                    <div class="admin-empty-state">This user has not placed any orders yet.</div>
                @else
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Placed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->orders as $order)
                                    <tr>
                                        <td class="fw-semibold">#{{ $order->id }}</td>
                                        <td>Rs. {{ number_format($order->total_amount, 2) }}</td>
                                        <td>
                                            <span class="admin-chip {{ $order->status === 'completed' ? 'completed' : ($order->status === 'cancelled' ? 'cancelled' : 'pending') }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $order->created_at?->format('d M Y') ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
