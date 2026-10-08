@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="auth-header">
        <h1>Alumni Login</h1>
        <p>Welcome back to NMTAFE Alumni Platform</p>
    </div>

    <!-- Session Status -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle"></i> Login Failed</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <x-auth-session-status class="mb-4 alert alert-success" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" novalidate>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email"
                value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="your@email.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password"
                name="password" required autocomplete="current-password" placeholder="Enter your password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-3">
            <div class="checkbox-nmtafe">
                <input id="remember_me" type="checkbox" name="remember">
                <label for="remember_me">Remember me</label>
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-nmtafe w-100 mb-3">
            <i class="fas fa-sign-in-alt"></i> Log In
        </button>

        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="auth-divider">
                <span>or</span>
            </div>
            <div class="text-center mb-3">
                <a href="{{ route('password.request') }}" class="text-link-secondary">Forgot your password?</a>
            </div>
        @endif
    </form>

    <!-- Register Link -->
    <div class="auth-footer">
        <p>Don't have an account? <a href="{{ route('register') }}">Sign up here</a></p>
    </div>
@endsection
