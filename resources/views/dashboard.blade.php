@php $pageTitle = 'Dashboard'; @endphp

@extends('layouts.platform')

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="nmtafe-hero rounded-4 p-4 p-lg-5">
                <div class="nmtafe-kicker text-danger fw-semibold mb-2">NMTAFE Alumni Platform</div>
                <h2 class="display-6 fw-bold mb-2 text-dark">Welcome back, {{ auth()->user()?->name }}</h2>
                <p class="text-secondary mb-4">Use the sidebar to navigate your alumni network.</p>

                <div class="d-flex flex-wrap gap-2">
                    {{-- <a href="{{ route('directory') }}" class="btn btn-danger btn-lg">Explore directory</a>
                    <a href="{{ route('directory') }}" class="btn btn-outline-dark btn-lg">Explore directory</a> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                <div class="nmtafe-kicker text-danger fw-semibold mb-2">Dashboard</div>
                <p class="text-secondary mb-0">The NMTAFE platform.</p>
            </div>
        </div>
    </div>
@endsection
