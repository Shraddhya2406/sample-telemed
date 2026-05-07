@extends('admin.layout')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
        <div class="mb-4">
            <h2 class="h5 admin-section-title mb-1">Update Category</h2>
            <p class="admin-muted mb-0">Refine the category metadata used across your medicine inventory.</p>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="row g-4">
            @csrf
            @method('PUT')

            <div class="col-12">
                <label for="name" class="form-label fw-semibold">Category Name</label>
                <input type="text" id="name" name="name" value="{{ old('name', $category->name) }}" class="form-control admin-form-control">
            </div>

            <div class="col-12">
                <label for="description" class="form-label fw-semibold">Description</label>
                <textarea id="description" name="description" class="form-control admin-form-control admin-textarea">{{ old('description', $category->description) }}</textarea>
            </div>

            <div class="col-12">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">Active and selectable for medicines</label>
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Category</button>
            </div>
        </form>
    </div>
</div>
@endsection
