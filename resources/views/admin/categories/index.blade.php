@extends('admin.layout')

@section('title', 'Categories')
@section('page-title', 'Medicine Categories')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h2 class="h5 admin-section-title mb-1">Categories</h2>
                <p class="admin-muted mb-0">Create and manage the medicine categories used across inventory.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-plus-circle me-2"></i>Add Category
            </a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table table-striped align-middle mb-0 js-admin-datatable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Medicines</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->name }}</td>
                            <td class="admin-muted">{{ $category->description ?: 'No description' }}</td>
                            <td>{{ $category->medicines_count }}</td>
                            <td>
                                <form action="{{ route('admin.categories.toggle-status', $category) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <div class="form-check form-switch m-0 d-inline-flex align-items-center gap-2">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="category_status_{{ $category->id }}"
                                            {{ $category->is_active ? 'checked' : '' }}
                                            onchange="this.form.submit()"
                                        >
                                        <label class="form-check-label small fw-semibold" for="category_status_{{ $category->id }}">
                                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </form>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a
                                        href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn btn-outline-secondary btn-sm rounded-circle"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Edit"
                                        aria-label="Edit"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="js-delete-confirm" data-confirm-text="Deleting this category cannot be undone.">
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
                            <td colspan="5" class="admin-empty-state">No categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
