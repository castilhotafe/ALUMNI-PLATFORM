@extends('layouts.auth')

@section('content')
    <!-- Header -->
    <div class="auth-header">
        <h1>Create Account</h1>
        <p>Join the NMTAFE Alumni Community</p>
    </div>

    <!-- Validation Errors -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle"></i> Registration Error</strong>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" novalidate>
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        <!-- Full Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Full Name</label>
            <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name"
                value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Enter your full name">
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email"
                value="{{ old('email') }}" required autocomplete="username" placeholder="your@email.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Role Selection -->
        <div class="mb-3">
            <label for="role_selection" class="form-label">Are you a current student, alumni, TAFE lecturer, Partner, or
                General User?</label>
            <select id="role_selection" class="form-select @error('role_selection') is-invalid @enderror"
                name="role_selection" required>
                <option value="" disabled {{ old('role_selection') ? '' : 'selected' }}>Select your role</option>
                <option value="current" {{ old('role_selection') === 'current' ? 'selected' : '' }}>Current student</option>
                <option value="alumni" {{ old('role_selection') === 'alumni' ? 'selected' : '' }}>Alumni</option>
                <option value="lecturer" {{ old('role_selection') === 'lecturer' ? 'selected' : '' }}>TAFE lecturer
                </option>
                <option value="partner" {{ old('role_selection') === 'partner' ? 'selected' : '' }}>Partner</option>
                <option value="general" {{ old('role_selection') === 'general' ? 'selected' : '' }}>General User</option>
            </select>
            @error('role_selection')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Student ID (Current Student or Alumni) -->
        <div class="mb-3 d-none" id="student_id_field">
            <label for="student_id" class="form-label">Student ID</label>
            <input id="student_id" class="form-control @error('student_id') is-invalid @enderror" type="text"
                name="student_id" value="{{ old('student_id') }}" inputmode="numeric" pattern="\d{8}" maxlength="8"
                placeholder="8-digit student ID">
            @error('student_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password"
                name="password" required autocomplete="new-password" placeholder="Create a strong password">
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror"
                type="password" name="password_confirmation" required autocomplete="new-password"
                placeholder="Confirm your password">
            @error('password_confirmation')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-nmtafe w-100 mb-3">
            <i class="fas fa-user-plus"></i> Create Account
        </button>
    </form>

    <!-- Login Link -->
    <div class="auth-footer">
        <p>Already have an account? <a href="{{ route('login') }}">Log in here</a></p>
    </div>

    <script>
        (() => {
            const roleSelect = document.getElementById('role_selection');
            const studentField = document.getElementById('student_id_field');
            const studentInput = document.getElementById('student_id');

            if (!roleSelect || !studentField || !studentInput) {
                return;
            }

            const updateStudentField = () => {
                const needsStudentId = ['current', 'alumni'].includes(roleSelect.value);
                studentField.classList.toggle('d-none', !needsStudentId);
                studentInput.required = needsStudentId;
                studentInput.disabled = !needsStudentId;
                if (!needsStudentId) {
                    studentInput.value = '';
                }
            };

            roleSelect.addEventListener('change', updateStudentField);
            updateStudentField();
        })();
    </script>
@endsection
