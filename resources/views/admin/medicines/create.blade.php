@extends('admin.layout')

@section('title', 'Create Medicine')
@section('page-title', 'Create Medicine')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
    <div class="mb-4">
        <h2 class="h5 admin-section-title mb-1">New Medicine</h2>
        <p class="admin-muted mb-0">Add a medicine with pricing, stock, and product imagery.</p>
    </div>

    <form action="{{ route('admin.medicines.store') }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf

        <div class="col-12">
            <label for="name" class="form-label fw-semibold">Medicine Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control admin-form-control">
            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="brand" class="form-label fw-semibold">Brand</label>
            <input type="text" id="brand" name="brand" value="{{ old('brand') }}" class="form-control admin-form-control">
            @error('brand') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="sku" class="form-label fw-semibold">SKU</label>
            <input type="text" id="sku" name="sku" value="{{ old('sku') }}" class="form-control admin-form-control" placeholder="MED-00001">
            @error('sku') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" class="form-control admin-form-control admin-textarea">{{ old('description') }}</textarea>
            @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="composition" class="form-label fw-semibold">Composition</label>
            <textarea id="composition" name="composition" class="form-control admin-form-control">{{ old('composition') }}</textarea>
            @error('composition') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="manufacturer" class="form-label fw-semibold">Manufacturer</label>
            <input type="text" id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}" class="form-control admin-form-control">
            @error('manufacturer') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="price" class="form-label fw-semibold">Price</label>
            <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price') }}" class="form-control admin-form-control">
            @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="stock" class="form-label fw-semibold">Stock</label>
            <input type="number" min="0" id="stock" name="stock" value="{{ old('stock') }}" class="form-control admin-form-control">
            @error('stock') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="expiry_date" class="form-label fw-semibold">Expiry Date</label>
            <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control admin-form-control">
            @error('expiry_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

            <div class="col-md-6">
                <label for="category_id" class="form-label fw-semibold">Category</label>
                <select id="category_id" name="category_id" class="form-select admin-form-select">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label fw-semibold">Primary Image</label>
                <input type="file" id="image" name="image" accept="image/*" class="form-control admin-form-control">
                <div class="form-text">This image will be used as the main thumbnail.</div>
                @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="images" class="form-label fw-semibold">Additional Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple class="form-control admin-form-control">
                <div class="form-text">You can upload up to 6 extra gallery images.</div>
                @error('images') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('images.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="is_active">Active and visible in store</label>
            </div>
            @error('is_active') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12 d-flex gap-2">
            <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Create Medicine</button>
        </div>
    </form>
    </div>
</div>
@endsection
