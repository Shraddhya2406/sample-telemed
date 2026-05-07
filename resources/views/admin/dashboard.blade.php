@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card admin-panel admin-stat-card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="admin-stat-label">Total Orders</span>
                    <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis"><i class="bi bi-bag-check me-1"></i>Orders</span>
                </div>
                <div class="admin-stat-value">{{ $stats['totalOrders'] }}</div>
                <p class="admin-muted mb-0 mt-2">All medicine purchases placed by patients.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card admin-panel admin-stat-card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="admin-stat-label">Total Medicines</span>
                    <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis"><i class="bi bi-capsule-pill me-1"></i>Inventory</span>
                </div>
                <div class="admin-stat-value">{{ $stats['totalMedicines'] }}</div>
                <p class="admin-muted mb-0 mt-2">Products available across the store catalog.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card admin-panel admin-stat-card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="admin-stat-label">Total Users</span>
                    <span class="badge rounded-pill text-bg-info-subtle text-info-emphasis"><i class="bi bi-people me-1"></i>Accounts</span>
                </div>
                <div class="admin-stat-value">{{ $stats['totalUsers'] }}</div>
                <p class="admin-muted mb-0 mt-2">Registered platform users with active access.</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card admin-panel h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h2 class="h5 admin-section-title mb-1">Recent Orders</h2>
                        <p class="admin-muted mb-0">Track the newest purchases and patient activity.</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary rounded-pill">View all</a>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Patient</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">#{{ $order->id }}</a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->user?->name }}</div>
                                        <div class="small admin-muted">{{ $order->user?->email }}</div>
                                    </td>
                                    <td>Rs. {{ number_format($order->total_amount, 2) }}</td>
                                    <td><span class="admin-chip {{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="admin-empty-state">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card admin-panel h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h2 class="h5 admin-section-title mb-1">Low Stock Watch</h2>
                        <p class="admin-muted mb-0">Quick view of medicines that may need replenishment.</p>
                    </div>
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline-success rounded-pill">Manage</a>
                </div>

                <div class="admin-card-list">
                    @forelse ($lowStockMedicines as $medicine)
                        <div class="admin-mini-card">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $medicine->name }}</div>
                                    <div class="small admin-muted">{{ $medicine->category ?: 'Uncategorized' }}</div>
                                </div>
                                <span class="badge rounded-pill text-bg-light border">Stock {{ $medicine->stock_quantity }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="admin-empty-state">No medicine data available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
