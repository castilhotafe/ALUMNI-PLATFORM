@extends('layouts.platform')

@section('content')
    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border">
        <div class="row align-items-end g-3">
            <div class="col-12 col-lg-8">
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Find people</div>
                <h2 class="h3 fw-bold mb-2 text-dark">Browse alumni and start connecting.</h2>
                <p class="text-secondary mb-0">Discover colleagues, reach out about collaboration, and explore who's active
                    in the network.</p>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
            </div>
        </div>
    </div>

    @include('components.search-bar', [
        'searchQuery' => $searchQuery ?? '',
        'selectedDetails' => $selectedDetails ?? [],
        'detailOptions' => $detailOptions ?? [],
        'label' => 'Search alumni',
        'placeholder' => 'Search by name, bio, or details',
        'buttonLabel' => 'Find Alumni',
        'inputId' => 'directory-search',
    ])

    <div class="row g-4">
        @forelse ($users as $directoryUser)
            <div class="col-12 col-md-6 col-xxl-4">
                <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                            style="width: 56px; height: 56px; font-size: 20px;">
                            {{ strtoupper(substr($directoryUser->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <div class="fw-semibold">{{ $directoryUser->name }}</div>
                            <div class="small text-secondary">
                                {{ $directoryUser->profile?->resume_link ? 'Resume available' : 'Profile active' }}</div>
                        </div>
                    </div>

                    <p class="text-secondary small mb-3">
                        {{ $directoryUser->profile?->bio ?? 'Complete alumni profile - no bio yet.' }}</p>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @forelse ($directoryUser->profile?->details ?? [] as $detail)
                            <span class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                        @empty
                            <span class="badge rounded-pill text-bg-light text-secondary small">No details listed</span>
                        @endforelse
                    </div>

                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="bg-light rounded-3 p-2">
                                <div class="fw-semibold small">{{ $directoryUser->posts->count() }}</div>
                                <div class="small text-secondary">Posts</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded-3 p-2">
                                <div class="fw-semibold small">{{ $directoryUser->projects_count }}</div>
                                <div class="small text-secondary">Projects</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded-3 p-2">
                                <div class="fw-semibold small">{{ $directoryUser->research_papers_count }}</div>
                                <div class="small text-secondary">Papers</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border text-center py-4">
                    {{ !empty($searchQuery) ? 'No alumni profiles match your search.' : 'No alumni profiles available yet. Check back soon.' }}
                </div>
            </div>
        @endforelse
    </div>
@endsection
