<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor Panel') - {{ config('app.name', 'Sample Telemed') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/css/admin.css'])
    <style>
        .doctor-shell { min-height: 100vh; background: #f4f8fb; }
        .doctor-sidebar { width: 280px; background: #0f766e; color: #fff; padding: 1.5rem; }
        .doctor-brand-kicker { margin: 0; font-size: .75rem; opacity: .75; text-transform: uppercase; }
        .doctor-brand-name { margin: 0; color: #fff; font-weight: 700; }
        .doctor-nav { display: grid; gap: .45rem; margin-top: 2rem; }
        .doctor-nav-link { display: flex; gap: .75rem; align-items: center; color: rgba(255,255,255,.86); padding: .75rem .9rem; border-radius: .5rem; text-decoration: none; }
        .doctor-nav-link:hover, .doctor-nav-link.active { color: #0f766e; background: #fff; }
        .doctor-main { flex: 1; min-width: 0; }
        .doctor-topbar { background: #fff; border-bottom: 1px solid #dbe7ee; }
        .doctor-card { background: #fff; border: 1px solid #dbe7ee; border-radius: .5rem; box-shadow: 0 8px 24px rgba(15, 118, 110, .07); }
        .status-dot { width: .6rem; height: .6rem; border-radius: 50%; display: inline-block; }
        @media (max-width: 991.98px) { .doctor-sidebar { width: 100%; } }
    </style>
</head>
<body>
    <div class="doctor-shell d-lg-flex">
        <aside class="doctor-sidebar">
            <a href="{{ route('doctor.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                <x-logo size="36" :showText="false" class="block" />
                <div>
                    <p class="doctor-brand-kicker">Doctor Panel</p>
                    <p class="doctor-brand-name">{{ config('app.name', 'Sample Telemed') }}</p>
                </div>
            </a>

            <nav class="doctor-nav">
                <a class="doctor-nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
                <a class="doctor-nav-link {{ request()->routeIs('doctor.patients.*') ? 'active' : '' }}" href="{{ route('doctor.patients.index') }}"><i class="bi bi-people-fill"></i><span>Patients</span></a>
                <a class="doctor-nav-link {{ request()->routeIs('doctor.appointments.*') ? 'active' : '' }}" href="{{ route('doctor.appointments.index') }}"><i class="bi bi-calendar2-check-fill"></i><span>Appointments</span></a>
                <a class="doctor-nav-link {{ request()->routeIs('doctor.prescriptions.*') ? 'active' : '' }}" href="{{ route('doctor.prescriptions.index') }}"><i class="bi bi-prescription2"></i><span>Prescriptions</span></a>
                <a class="doctor-nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}" href="{{ route('doctor.dashboard') }}#availability"><i class="bi bi-clock-history"></i><span>Profile</span></a>
            </nav>

            <div class="mt-5 pt-4 border-top border-white border-opacity-25">
                <div class="small opacity-75">{{ auth()->user()->email }}</div>
                <div class="fw-semibold mb-3">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-light w-100" type="submit"><i class="bi bi-box-arrow-right me-1"></i> Logout</button>
                </form>
            </div>
        </aside>

        <div class="doctor-main">
            <header class="doctor-topbar px-4 px-xl-5 py-4">
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                    <div>
                        <div class="text-uppercase small text-secondary fw-semibold">@yield('kicker', 'Clinical Workspace')</div>
                        <h1 class="h3 mb-0">@yield('page-title', 'Doctor Panel')</h1>
                    </div>
                    <a href="{{ route('doctor.prescriptions.create') }}" class="btn btn-success align-self-start"><i class="bi bi-plus-circle me-1"></i> Prescription</a>
                </div>
            </header>

            <main class="px-4 px-xl-5 py-4">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
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
</body>
</html>
