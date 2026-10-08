@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="auth-header">
        <h1>Verify Email</h1>
        <p>Complete your account setup</p>
    </div>

    <!-- Information Message -->
    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-envelope-open"></i>
        <strong>Thanks for signing up!</strong> Before getting started, please verify your email address by clicking on the
        link we just sent you. If you didn't receive the email, we'll gladly send you another.
    </div>

    <!-- Success Message -->
    @if (session('status') == 'Verification link sent')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i>
            <strong>Success!</strong> A new verification link has been sent to the email address you provided.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Resend Email Form -->
    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button type="submit" class="btn btn-nmtafe w-100">
            <i class="fas fa-redo"></i> Resend Verification Email
        </button>
    </form>

    <!-- Logout Form -->
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary w-100">
            <i class="fas fa-sign-out-alt"></i> Log Out
        </button>
    </form>
@endsection
