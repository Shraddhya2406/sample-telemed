@extends('admin.layout')

@section('title', 'Medicines')
@section('page-title', 'Medicine Management')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 admin-section-title mb-1">Medicines</h2>
            <p class="admin-muted mb-0">Manage product data, stock levels, and store visibility.</p>
        </div>
        <a href="{{ route('admin.medicines.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-circle me-2"></i>Add Medicine
        </a>
    </div>

    <div class="table-responsive">
        <table class="table admin-table table-striped align-middle mb-0 js-admin-datatable">
            <thead>
                <tr>
                    <th>Medicine</th>
                    <th>SKU</th>
                    <th>Brand</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($medicines as $medicine)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="admin-thumb">
                                <div>
                                    <div class="fw-semibold">{{ $medicine->name }}</div>
                                    <div class="small admin-muted">{{ $medicine->category_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $medicine->sku ?: '-' }}</td>
                        <td>{{ $medicine->brand ?: '-' }}</td>
                        <td>Rs. {{ number_format($medicine->price, 2) }}</td>
                        <td>{{ $medicine->stock_quantity }}</td>
                        <td>{{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}</td>
                        <td>
                            <form action="{{ route('admin.medicines.toggle-status', $medicine) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="status_{{ $medicine->id }}"
                                        {{ $medicine->is_active ? 'checked' : '' }}
                                        onchange="this.form.submit()"
                                    >
                                    <label class="form-check-label small fw-semibold" for="status_{{ $medicine->id }}">
                                        {{ $medicine->is_active ? 'Active' : 'Inactive' }}
                                    </label>
                                </div>
                            </form>
                        </td>
                        <td>
                            <div class="admin-actions">
                                <a
                                    href="{{ route('admin.medicines.show', $medicine) }}"
                                    class="btn btn-outline-primary btn-sm rounded-circle"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="View"
                                    aria-label="View"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a
                                    href="{{ route('admin.medicines.edit', $medicine) }}"
                                    class="btn btn-outline-secondary btn-sm rounded-circle"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Edit"
                                    aria-label="Edit"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('admin.medicines.destroy', $medicine) }}" method="POST" class="js-delete-confirm" data-confirm-text="This medicine and its images will be removed.">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="btn btn-outline-danger btn-sm rounded-circle"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Delete"
                                        aria-label="Delete"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-empty-state">No medicines found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
@endsection
