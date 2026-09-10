<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b10f19">

    <title>{{ config('app.name', 'NMTAFE Alumni') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    @vite(['resources/css/app.scss', 'resources/js/app.js'])

</head>

<body class="alumni-home">
    <nav class="alumni-nav py-3 w-100">
        <div class="container-fluid px-3 px-lg-5 d-flex align-items-center justify-content-between gap-3">
            <a class="d-inline-flex align-items-center gap-3 text-decoration-none text-dark" href="{{ url('/') }}">
                <img src="{{ asset('branding/nmtafe-logo-white.svg') }}" alt="North Metropolitan TAFE"
                    class="alumni-logo bg-dark rounded-3 p-2">
                <div class="d-none d-sm-block">
                    <div class="fw-bold text-uppercase small text-danger">Alumni App</div>
                    <div class="text-secondary small">North Metropolitan TAFE</div>
                </div>
            </a>

            <div class="d-flex align-items-center gap-2 gap-sm-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-dark rounded-pill px-4 fw-semibold">Go to
                        dashboard</a>
                @endauth

                @guest
                    {{-- Stub routes for login and register until views are done --}}
                    <a href="" class="btn btn-outline-dark rounded-pill px-4 fw-semibold">Log in</a>
                    <a href="" class="btn btn-danger rounded-pill px-4 fw-semibold">Register</a>
                @endguest
            </div>
        </div>
    </nav>
</body>

</html>
