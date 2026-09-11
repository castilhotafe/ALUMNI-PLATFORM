@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="auth-header">
        <h1>Reset Password</h1>
        <p>We'll help you get back into your account</p>
    </div>

    <!-- Information Message -->
    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-info-circle"></i> Enter your email address and we'll send you a link to reset your password.
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   type="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autofocus
                   placeholder="your@email.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-nmtafe w-100 mb-3">
            <i class="fas fa-envelope"></i> Send Reset Link
        </button>
    </form>

    <!-- Back to Login -->
    <div class="auth-footer">
        <p><a href="{{ route('login') }}" class="text-link-secondary"><i class="fas fa-arrow-left"></i> Back to login</a></p>
    </div>
@endsection