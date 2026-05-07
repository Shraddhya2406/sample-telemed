@extends('admin.layout')

@section('title', 'Edit User')
@section('page-title', 'Edit User Role')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-7">
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
    <div class="mb-6">
        <h2 class="h5 admin-section-title mb-1">{{ $user->name }}</h2>
        <p class="admin-muted mb-0">{{ $user->email }}</p>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="d-grid gap-4">
        @csrf
        @method('PUT')

        <div>
            <label for="role_id" class="form-label fw-semibold">Role</label>
            <select id="role_id" name="role_id" class="form-select admin-form-select">
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">Save Role</button>
        </div>
    </form>
</div>
</div>
</div>
</div>
@endsection
