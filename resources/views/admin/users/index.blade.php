@extends('admin.layout')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="card admin-panel">
    <div class="card-body p-4 p-xl-5">
    <div class="mb-6">
        <h2 class="h5 admin-section-title mb-1">Users</h2>
        <p class="admin-muted mb-0">Manage user access and admin permissions across the platform.</p>
    </div>

    <div class="table-responsive">
        <table class="table admin-table table-striped align-middle mb-0 js-admin-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>#{{ $user->id }}</td>
                        <td class="fw-semibold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="admin-chip {{ $user->role?->name === 'admin' ? 'admin-role' : 'patient-role' }}">
                                {{ ucfirst($user->role?->name ?? 'No role') }}
                            </span>
                        </td>
                        <td>{{ $user->orders_count }}</td>
                        <td>{{ $user->created_at?->format('d M Y') ?? 'N/A' }}</td>
                        <td class="text-end">
                            <div class="admin-actions">
                                <a
                                    href="{{ route('admin.users.show', $user) }}"
                                    class="btn btn-outline-primary btn-sm rounded-circle"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="View"
                                    aria-label="View"
                                >
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a
                                    href="{{ route('admin.users.edit', $user) }}"
                                    class="btn btn-outline-secondary btn-sm rounded-circle"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Edit Role"
                                    aria-label="Edit Role"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="admin-empty-state">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
@endsection
