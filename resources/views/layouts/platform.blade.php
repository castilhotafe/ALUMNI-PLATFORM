<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body class="text-body">
    @php
        $roleLabel = auth()->user()?->getRoleNames()->first() ?? 'Member';

        // Navigation items with placeholder routes until views and named routes are defined
        $adminNavigationItems = auth()->user()?->hasRole('Admin')
            ? [
                ['label' => 'Admin Dashboard', 'route' => 'admin.dashboard'],
                ['label' => 'Contribution Ledger', 'route' => 'admin.contributions.index'],
            ]
            : [];

        $navigationItems = [
            ['label' => 'Dashboard', 'route' => 'dashboard'],
            ['label' => 'Me', 'route' => 'me'],
            ['label' => 'Developer Directory', 'route' => 'directory'],
            ['label' => 'Projects', 'route' => 'projects'],
            ['label' => 'Research Papers', 'route' => 'papers'],
            ['label' => 'Networking Hub', 'route' => 'networking'],
            ['label' => 'Discover Hub', 'route' => 'discover'],
            ['label' => 'Messages', 'route' => 'messages'],
            ['label' => 'Profile', 'route' => 'profile.edit'],
        ];
    @endphp

    <div class="d-flex min-vh-100 w-100">
        <aside class="nmtafe-sidebar d-none d-lg-flex flex-column p-4">
            <a class="text-decoration-none" href="#">
                <img src="{{ asset('branding/nmtafe-logo-white.svg') }}" alt="NMTAFE Alumni Platform"
                    class="nmtafe-brand-mark mb-4">
            </a>

            <div class="small text-uppercase opacity-50 fw-semibold mb-3">Alumni platform</div>

            <nav class="nav nav-pills flex-column gap-1">
                @foreach ($navigationItems as $item)
                    <a class="nav-link px-3 py-2 {{ Route::has($item['route']) && request()->routeIs($item['route']) ? 'active' : '' }}"
                        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                @foreach ($adminNavigationItems as $item)
                    <a class="nav-link px-3 py-2 {{ Route::has($item['route']) && request()->routeIs($item['route']) ? 'active' : '' }}"
                        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            @auth
                <div class="mt-4">
                    <x-contribution-indicator :user="auth()->user()" compact />
                </div>
            @endauth

            <div class="mt-auto pt-4">
                <div class="small text-uppercase opacity-50">Signed in as</div>
                <div class="fw-semibold">{{ auth()->user()?->name }}</div>
                <div class="small text-uppercase opacity-50">{{ $roleLabel }}</div>
                <div class="mt-3">
                    <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Logout</button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="platform-main flex-grow-1 min-vw-0">
            <header class="platform-header sticky-top">
                <div class="platform-header-content">
                    <div class="platform-header-left">
                        <button class="btn btn-outline-danger d-lg-none" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#mobileNav" aria-controls="mobileNav">
                            Menu
                        </button>
                        <div class="platform-header-title">
                            <div class="nmtafe-kicker text-danger fw-semibold">{{ $sectionKicker ?? 'Overview' }}</div>
                            <h1 class="h4 mb-0">{{ $pageTitle ?? config('app.name') }}</h1>
                        </div>
                    </div>

                    <div class="platform-header-right">
                        <span class="badge rounded-pill text-bg-light nmtafe-pill">{{ $roleLabel }}</span>
                        <span class="badge rounded-pill text-bg-danger nmtafe-pill">Blackboard</span>
                        @auth
                            <x-contribution-indicator :user="auth()->user()" compact class="flex-shrink-0" />
                        @endauth
                    </div>
                </div>
            </header>

            <main class="container-fluid p-3 p-lg-4 platform-main-shell">
                @if (session('status'))
                    <div class="alert alert-success mb-4">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <div class="fw-semibold mb-2">Please fix the following:</div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="platform-content-shell">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <div role="dialog" class="offcanvas offcanvas-start nmtafe-sidebar text-white" tabindex="-1" id="mobileNav"
        aria-labelledby="mobileNavLabel">
        <div class="offcanvas-header border-bottom border-white border-opacity-10">
            <div class="h5 offcanvas-title" id="mobileNavLabel">NMTAFE Alumni Platform</div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-4">
            <nav class="nav nav-pills flex-column gap-1">
                @foreach ($navigationItems as $item)
                    <a class="nav-link px-3 py-2 {{ Route::has($item['route']) && request()->routeIs($item['route']) ? 'active' : '' }}"
                        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                @foreach ($adminNavigationItems as $item)
                    <a class="nav-link px-3 py-2 {{ Route::has($item['route']) && request()->routeIs($item['route']) ? 'active' : '' }}"
                        href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            @auth
                <x-contribution-indicator :user="auth()->user()" compact class="mt-1" />
            @endauth

            <div class="mt-auto">
                <div class="small text-uppercase opacity-50">Signed in as</div>
                <div class="fw-semibold">{{ auth()->user()?->name }}</div>
                <div class="small text-uppercase opacity-50">{{ $roleLabel }}</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
