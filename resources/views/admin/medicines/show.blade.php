@extends('admin.layout')

@section('title', 'Medicine Details')
@section('page-title', 'Medicine Details')

@section('content')
<div class="row g-4">
    <div class="col-xl-7">
        <div class="card admin-panel">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h4 mb-1">{{ $medicine->name }}</h2>
                        <p class="admin-muted mb-0">{{ $medicine->category_name }}</p>
                    </div>
                    <span class="admin-chip {{ $medicine->is_active ? 'active' : 'inactive' }}">
                        {{ $medicine->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small admin-muted">Brand</div>
                        <div class="fw-semibold">{{ $medicine->brand ?: '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small admin-muted">SKU</div>
                        <div class="fw-semibold">{{ $medicine->sku ?: '-' }}</div>
                    </div>
                </div>

                <img src="{{ $medicine->image_url }}" alt="{{ $medicine->name }}" class="img-fluid rounded-4 border mb-4" style="width: 100%; max-height: 360px; object-fit: cover;">

                <div class="row g-3 mb-4">
                    @foreach ($medicine->images as $image)
                        <div class="col-md-4">
                            <img src="{{ Str::startsWith($image->image_path, 'images/') ? asset($image->image_path) : route('media.public', ['path' => $image->image_path]) }}" alt="{{ $medicine->name }}" class="img-fluid rounded-4 border {{ $image->is_thumbnail ? 'border-primary border-2' : '' }}" style="height: 140px; width: 100%; object-fit: cover;">
                        </div>
                    @endforeach
                </div>

                <h3 class="h6">Description</h3>
                <p>{{ $medicine->description ?: 'No description provided.' }}</p>

                <h3 class="h6">Composition</h3>
                <p>{{ $medicine->composition ?: 'No composition provided.' }}</p>

                <h3 class="h6">Manufacturer</h3>
                <p class="mb-0">{{ $medicine->manufacturer ?: 'No manufacturer provided.' }}</p>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card admin-panel">
            <div class="card-body p-4">
                <h3 class="h5 admin-section-title mb-3">Inventory Summary</h3>
                <div class="mb-3">
                    <div class="small admin-muted">Price</div>
                    <div class="fw-semibold">Rs. {{ number_format($medicine->price, 2) }}</div>
                </div>
                <div class="mb-3">
                    <div class="small admin-muted">Stock Quantity</div>
                    <div class="fw-semibold">{{ $medicine->stock_quantity }}</div>
                </div>
                <div class="mb-3">
                    <div class="small admin-muted">Expiry Date</div>
                    <div class="fw-semibold">{{ optional($medicine->expiry_date)->format('d M Y') ?: '-' }}</div>
                </div>
                <div class="mb-4">
                    <div class="small admin-muted">Images</div>
                    <div class="fw-semibold">{{ $medicine->images->count() }}</div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('admin.medicines.edit', $medicine) }}" class="btn btn-primary rounded-pill px-4">Edit</a>
                    <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
