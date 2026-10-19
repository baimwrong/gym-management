<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('gym.name', 'Gym Management') }}</title>

    <!-- Local Offline Assets - Zero Internet Required -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    @stack('styles')
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="app-sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-dumbbell"></i>
                <div class="text-truncate">
                    <div style="font-size: 1rem; line-height: 1.2; font-weight: 800;">{{ config('gym.short_name', 'GYMPRO') }}</div>
                    <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 600;">FITNESS CLUB</div>
                </div>
            </div>

            @include('layouts.sidebar-nav')

            <div class="sidebar-user">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2 overflow-hidden">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 700;">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-truncate">
                            <div class="text-white fw-semibold small text-truncate">{{ auth()->user()->name ?? 'Staff User' }}</div>
                            <div class="badge bg-secondary" style="font-size: 0.65rem;">{{ strtoupper(auth()->user()->role ?? 'STAFF') }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-light border-0" title="Logout">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Mobile Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-start text-white d-md-none" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel" style="width: 280px; background-color: var(--gym-sidebar-bg);">
            <div class="offcanvas-header border-bottom border-secondary border-opacity-25 py-3">
                <div class="d-flex align-items-center gap-2" id="mobileSidebarLabel">
                    <i class="fa-solid fa-dumbbell text-info fs-4"></i>
                    <div>
                        <div class="fw-bold text-white fs-6">{{ config('gym.short_name', 'GYMPRO') }}</div>
                        <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 600;">FITNESS CLUB</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0 d-flex flex-column justify-content-between overflow-y-auto">
                @include('layouts.sidebar-nav')

                <div class="sidebar-user">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-weight: 700;">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div class="text-truncate">
                                <div class="text-white fw-semibold small text-truncate">{{ auth()->user()->name ?? 'Staff User' }}</div>
                                <div class="badge bg-secondary" style="font-size: 0.65rem;">{{ strtoupper(auth()->user()->role ?? 'STAFF') }}</div>
                            </div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-light border-0" title="Logout">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Body -->
        <main class="app-main">
            <!-- Topbar -->
            <header class="app-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <h5 class="mb-0 fw-bold text-slate-800">@yield('page_title', 'Dashboard')</h5>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('attendance.kiosk') }}" class="btn btn-sm btn-warning fw-semibold shadow-sm">
                        <i class="fa-solid fa-qrcode me-1"></i> Quick Turnstile
                    </a>
                    <a href="{{ route('members.create') }}" class="btn btn-sm btn-primary fw-semibold shadow-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> Register Member
                    </a>
                    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-dark fw-semibold shadow-sm">
                        <i class="fa-solid fa-cash-register me-1"></i> POS Register
                    </a>
                </div>
            </header>

            <!-- Alerts Container -->
            <div class="px-4 pt-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fa-solid fa-circle-info me-2"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <section class="page-content">
                @yield('content')
            </section>

            <!-- Footer -->
            <footer class="py-3 px-4 bg-white border-top text-muted small d-flex justify-content-between align-items-center">
                <div>
                    <strong>{{ config('gym.name') }}</strong> &copy; {{ date('Y') }}. Local Offline Intranet Edition.
                </div>
                <div class="d-flex gap-3">
                    <span class="badge bg-light text-dark border">
                        <i class="fa-solid fa-database text-success me-1"></i> PostgreSQL 18
                    </span>
                    <span class="badge bg-light text-dark border">
                        <i class="fa-brands fa-laravel text-danger me-1"></i> Laravel 13
                    </span>
                    <span class="badge bg-light text-dark border">
                        <i class="fa-solid fa-wifi text-muted me-1"></i> 100% Offline Ready
                    </span>
                </div>
            </footer>
        </main>
    </div>

    <!-- Local Offline Scripts -->
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
