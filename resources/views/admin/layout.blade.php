<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - {{ config('app.name', 'Sample Telemed') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
</head>
<body class="admin-portal">
    <div class="admin-shell d-lg-flex">
        <aside class="admin-sidebar d-flex flex-column">
            <div class="admin-brand">
                <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                        <x-logo size="34" :showText="false" class="block" />
                        <div>
                            <p class="admin-brand-kicker">Control Center</p>
                            <p class="admin-brand-name">{{ config('app.name', 'Sample Telemed') }}</p>
                        </div>
                    </a>
                </div>

                <nav class="admin-nav">
                    <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="admin-nav-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                        <span>Dashboard</span>
                    </a>
                <a href="{{ route('admin.medicines.index') }}" class="admin-nav-link {{ request()->routeIs('admin.medicines.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="bi bi-capsule-pill"></i></span>
                    <span>Medicines</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="admin-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="bi bi-tags-fill"></i></span>
                    <span>Categories</span>
                </a>
                <a href="{{ route('admin.orders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <span class="admin-nav-icon"><i class="bi bi-bag-check-fill"></i></span>
                    <span>Orders</span>
                </a>
                    <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <span class="admin-nav-icon"><i class="bi bi-people-fill"></i></span>
                        <span>Users</span>
                    </a>
                </nav>

                <div class="admin-sidebar-footer">
                    <div class="admin-profile-card">
                        <div class="admin-profile-name">{{ auth()->user()->name }}</div>
                        <div class="admin-profile-email">{{ auth()->user()->email }}</div>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('dashboard.patient') }}" class="btn btn-outline-light rounded-pill">Patient Area</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100 rounded-pill">Logout</button>
                        </form>
                    </div>
                </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar px-4 px-xl-5 py-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <p class="admin-page-kicker">Medicine Store Admin</p>
                        <h1 class="admin-page-title">@yield('page-title', 'Admin Panel')</h1>
                    </div>
                    <div class="badge text-bg-light border rounded-pill px-3 py-2">
                        <i class="bi bi-calendar3 me-2"></i>{{ now()->format('d M Y, h:i A') }}
                    </div>
                </div>
            </header>

            <main class="px-4 px-xl-5 py-4 py-xl-5">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.js-admin-datatable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [],
                language: {
                    search: 'Search:',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    zeroRecords: 'No matching records found',
                }
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: @json(session('success')),
                    timer: 2200,
                    showConfirmButton: false,
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: @json(session('error')),
                });
            @endif

            document.querySelectorAll('.js-delete-confirm').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: 'Are you sure?',
                        text: form.dataset.confirmText || 'This action cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Yes, delete it',
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
                new bootstrap.Tooltip(element);
            });
        });
    </script>
</body>
</html>
