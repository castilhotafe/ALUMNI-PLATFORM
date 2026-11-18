@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="auth-header">
        <h1>Confirm Password</h1>
        <p>This is a secure area. Please confirm your password.</p>
    </div>

    <!-- Information Message -->
    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-shield-alt"></i> For your security, please confirm your password before continuing.
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle"></i> Error</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password"
                name="password" required autocomplete="current-password" placeholder="Enter your password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-nmtafe w-100">
            <i class="fas fa-check-circle"></i> Confirm
        </button>
    </form>
@endsection
