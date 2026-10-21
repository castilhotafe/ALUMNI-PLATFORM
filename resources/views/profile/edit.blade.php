@php $pageTitle = 'Profile'; @endphp

@extends('layouts.platform')

@section('content')
    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border">
        <div class="nmtafe-kicker text-danger fw-semibold mb-3">Account settings</div>
        <h2 class="h3 fw-bold mb-2 text-dark">Manage your profile and security.</h2>
        <p class="text-secondary mb-0">Update your account details and keep your alumni profile secure.</p>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-4">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                        style="width: 64px; height: 64px; font-size: 24px;">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}</div>
                    <div class="min-w-0">
                        <div class="h5 mb-0">{{ auth()->user()?->name }}</div>
                        <div class="small text-secondary text-truncate">{{ auth()->user()?->email }}</div>
                    </div>
                </div>

                <div class="nmtafe-divider my-4"></div>

                <div class="small text-uppercase text-secondary fw-semibold mb-3">Quick links</div>
                <a href="{{ route('dashboard') }}" class="btn btn-danger w-100 mb-2">Go back to Dashboard</a>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="nmtafe-card bg-white rounded-4 p-4 mb-4">
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Profile information</div>
                <h3 class="h5 mb-4">Update your public profile details</h3>
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="nmtafe-card bg-white rounded-4 p-4 mb-4">
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Security</div>
                <h3 class="h5 mb-4">Change your password to keep your account secure</h3>
                @include('profile.partials.update-password-form')
            </div>

            <div class="nmtafe-card bg-white rounded-4 p-4">
                <div class="nmtafe-kicker text-danger fw-semibold mb-3 text-danger">Danger zone</div>
                <h3 class="h5 mb-4">Delete your account permanently</h3>
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
@endsection
