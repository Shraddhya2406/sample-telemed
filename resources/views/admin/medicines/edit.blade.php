@extends('admin.layout')

@section('title', 'Edit Medicine')
@section('page-title', 'Edit Medicine')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
    <div class="mb-4">
        <h2 class="h5 admin-section-title mb-1">Update Medicine</h2>
        <p class="admin-muted mb-0">Refresh product information, inventory, or store status.</p>
    </div>

    <form action="{{ route('admin.medicines.update', $medicine) }}" method="POST" enctype="multipart/form-data" class="row g-4">
        @csrf
        @method('PUT')

        <div class="col-12">
            <label for="name" class="form-label fw-semibold">Medicine Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $medicine->name) }}" class="form-control admin-form-control">
            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="brand" class="form-label fw-semibold">Brand</label>
            <input type="text" id="brand" name="brand" value="{{ old('brand', $medicine->brand) }}" class="form-control admin-form-control">
            @error('brand') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="sku" class="form-label fw-semibold">SKU</label>
            <input type="text" id="sku" name="sku" value="{{ old('sku', $medicine->sku) }}" class="form-control admin-form-control">
            @error('sku') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea id="description" name="description" class="form-control admin-form-control admin-textarea">{{ old('description', $medicine->description) }}</textarea>
            @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="composition" class="form-label fw-semibold">Composition</label>
            <textarea id="composition" name="composition" class="form-control admin-form-control">{{ old('composition', $medicine->composition) }}</textarea>
            @error('composition') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="manufacturer" class="form-label fw-semibold">Manufacturer</label>
            <input type="text" id="manufacturer" name="manufacturer" value="{{ old('manufacturer', $medicine->manufacturer) }}" class="form-control admin-form-control">
            @error('manufacturer') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="price" class="form-label fw-semibold">Price</label>
            <input type="number" step="0.01" min="0" id="price" name="price" value="{{ old('price', $medicine->price) }}" class="form-control admin-form-control">
            @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="stock" class="form-label fw-semibold">Stock</label>
            <input type="number" min="0" id="stock" name="stock" value="{{ old('stock', $medicine->stock_quantity) }}" class="form-control admin-form-control">
            @error('stock') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="expiry_date" class="form-label fw-semibold">Expiry Date</label>
            <input type="date" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', optional($medicine->expiry_date)->format('Y-m-d')) }}" class="form-control admin-form-control">
            @error('expiry_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

            <div class="col-md-6">
                <label for="category_id" class="form-label fw-semibold">Category</label>
                <select id="category_id" name="category_id" class="form-select admin-form-select">
                    <option value="">Select category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $medicine->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="image" class="form-label fw-semibold">Replace Primary Image</label>
                <input type="file" id="image" name="image" accept="image/*" class="form-control admin-form-control">
                <div class="form-text">Uploading here sets the new main thumbnail without removing the gallery.</div>
                @error('image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label for="images" class="form-label fw-semibold">Add More Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple class="form-control admin-form-control">
                <div class="form-text">Upload additional images. You can choose any image below as the thumbnail.</div>
                @error('images') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                @error('images.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

        <div class="col-12">
            <div class="p-3 border rounded-4 bg-light-subtle">
                <div class="fw-semibold mb-3">Image Gallery</div>
                <div class="row g-3">
                    @forelse ($medicine->images as $image)
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 h-100">
                                <img src="{{ Str::startsWith($image->image_path, 'images/') ? asset($image->image_path) : route('media.public', ['path' => $image->image_path]) }}" alt="{{ $medicine->name }}" class="img-fluid rounded-4 mb-3" style="height: 180px; width: 100%; object-fit: cover;">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="thumbnail_image_id" id="thumbnail_{{ $image->id }}" value="{{ $image->id }}" {{ old('thumbnail_image_id', $image->is_thumbnail ? $image->id : null) == $image->id ? 'checked' : '' }}>
                                    <label class="form-check-label" for="thumbnail_{{ $image->id }}">Use as thumbnail</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_image_ids[]" id="remove_{{ $image->id }}" value="{{ $image->id }}">
                                    <label class="form-check-label text-danger" for="remove_{{ $image->id }}">Remove image</label>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="admin-muted">No uploaded images yet.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $medicine->is_active) ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="is_active">Active and visible in store</label>
            </div>
            @error('is_active') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="col-12 d-flex gap-2">
            <a href="{{ route('admin.medicines.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
        </div>
    </form>
    </div>
</div>
@endsection
