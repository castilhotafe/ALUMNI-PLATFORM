@extends('layouts.platform')

@section('content')
    @php($headerTab = 'overview')
    @if (
        $errors->getBag('job_listing')->any() ||
            $errors->getBag('enrolment_listing')->any() ||
            session('status_job_listing') ||
            session('status_enrolment_listing'))
        @php($headerTab = 'create')
    @endif
    @if (session('status_job_listing_manage') || session('status_enrolment_listing_manage'))
        @php($headerTab = 'manage')
    @endif

    @php($jobFormHasErrors = $errors->getBag('job_listing')->any())
    @php($enrolmentFormHasErrors = $errors->getBag('enrolment_listing')->any())
    @php($jobProfileDetails = old('job_profile_details'))
    @php($jobProfileDetails = is_array($jobProfileDetails) && count($jobProfileDetails) ? $jobProfileDetails : [''])
    @php($enrolmentProfileDetails = old('enrolment_profile_details'))
    @php($enrolmentProfileDetails = is_array($enrolmentProfileDetails) && count($enrolmentProfileDetails) ? $enrolmentProfileDetails : [''])

    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border" data-x-data="sectionTabs('{{ $headerTab }}')">
        <div class="d-md-flex align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Discover Hub</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Trusted partners and admin can publish jobs and learning
                    opportunities.</h2>
                <p class="lead text-secondary mb-0">This page is the alumni bridge to industry listings and professional
                    development opportunities.</p>
                <div class="mt-3">
                    @if ($canPostListings)
                        <div class="alert alert-success mb-0">Your role can publish listings here.</div>
                    @else
                        <div class="alert alert-light border mb-0">Admin, Partner, and Lecturer accounts can publish these
                            listings.</div>
                    @endif
                </div>
            </div>

            <ul class="nav nav-pills mt-4 mt-md-0 p-1 rounded-3 flex-nowrap" id="discoverHeaderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border" id="discover-overview-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#discover-overview-pane" data-x-on:click="activate('overview')"
                        data-x-bind:class="activeTab === 'overview' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="discover-overview-pane"
                        data-x-bind:aria-selected="activeTab === 'overview'">Overview</button>
                </li>
                @can('create', \App\Models\JobListing::class)
                    <li class="nav-item" role="presentation">
                        <button class="btn btn-sm border ms-2 text-nowrap" id="discover-create-tab" type="button"
                            role="tab" data-bs-toggle="tab" data-bs-target="#discover-create-pane"
                            data-x-on:click="activate('create')"
                            data-x-bind:class="activeTab === 'create' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                            aria-controls="discover-create-pane" data-x-bind:aria-selected="activeTab === 'create'">
                            <i class="bi bi-plus-circle me-1"></i> Create Listings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="btn btn-sm border ms-2 text-nowrap" id="discover-manage-tab" type="button"
                            role="tab" data-bs-toggle="tab" data-bs-target="#discover-manage-pane"
                            data-x-on:click="activate('manage')"
                            data-x-bind:class="activeTab === 'manage' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                            aria-controls="discover-manage-pane" data-x-bind:aria-selected="activeTab === 'manage'">
                            <i class="bi bi-sliders me-1"></i> Manage Listings
                        </button>
                    </li>
                @endcan
            </ul>
        </div>

        <div class="tab-content" id="discoverHeaderTabsContent">
            <div class="tab-pane fade {{ $headerTab === 'overview' ? 'show active' : '' }}" id="discover-overview-pane"
                role="tabpanel" aria-labelledby="discover-overview-tab" tabindex="0"
                data-x-show="activeTab === 'overview'" data-x-bind:class="activeTab === 'overview' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Browse live job and enrolment
                    listings from trusted partners.</p>
            </div>

            @can('create', \App\Models\JobListing::class)
                <div class="tab-pane fade {{ $headerTab === 'create' ? 'show active' : '' }}" id="discover-create-pane"
                    role="tabpanel" aria-labelledby="discover-create-tab" tabindex="0" data-x-show="activeTab === 'create'"
                    data-x-bind:class="activeTab === 'create' ? 'show active' : ''">
                    <div class="pt-4 border-top">
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                                    <div class="nmtafe-kicker text-danger fw-semibold mb-2">Create a job listing</div>
                                    @if (session('status_job_listing'))
                                        <div class="alert alert-success py-2 small mb-3">{{ session('status_job_listing') }}
                                        </div>
                                    @endif
                                    @if ($errors->getBag('job_listing')->any())
                                        <div class="alert alert-danger py-2 small mb-3">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->getBag('job_listing')->all() as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('job-listings.store') }}" class="row g-3">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Title</label>
                                            <input type="text" name="title" class="form-control"
                                                value="{{ $jobFormHasErrors ? old('title') : '' }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Company</label>
                                            <input type="text" name="company" class="form-control"
                                                value="{{ $jobFormHasErrors ? old('company') : '' }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3" required>{{ $jobFormHasErrors ? old('description') : '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Apply link</label>
                                            <input type="url" name="apply_link" class="form-control"
                                                value="{{ $jobFormHasErrors ? old('apply_link') : '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Profile summary</label>
                                            <textarea name="job_profile_bio" class="form-control" rows="3">{{ $jobFormHasErrors ? old('job_profile_bio') : '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Profile picture URL</label>
                                            <input type="url" name="job_picture_url" class="form-control"
                                                value="{{ $jobFormHasErrors ? old('job_picture_url') : '' }}">
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label mb-0">Details</label>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-add-profile-detail="job-profile-details-fields">Add detail</button>
                                            </div>
                                            <div class="d-grid gap-2 mt-2" id="job-profile-details-fields"
                                                data-input-prefix="job_profile_details"
                                                data-next-index="{{ count($jobProfileDetails) }}">
                                                @foreach ($jobProfileDetails as $index => $detail)
                                                    <div class="row g-2 detail-row">
                                                        <div class="col">
                                                            <input type="text"
                                                                name="job_profile_details[{{ $index }}]"
                                                                class="form-control" value="{{ $detail }}"
                                                                placeholder="Detail">
                                                        </div>
                                                        <div class="col-auto text-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-danger" type="submit">Save job listing</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                                    <div class="nmtafe-kicker text-danger fw-semibold mb-2">Create an enrolment listing</div>
                                    @if (session('status_enrolment_listing'))
                                        <div class="alert alert-success py-2 small mb-3">
                                            {{ session('status_enrolment_listing') }}</div>
                                    @endif
                                    @if ($errors->getBag('enrolment_listing')->any())
                                        <div class="alert alert-danger py-2 small mb-3">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->getBag('enrolment_listing')->all() as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <form method="POST" action="{{ route('enrolment-listings.store') }}" class="row g-3">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Course title</label>
                                            <input type="text" name="course_title" class="form-control"
                                                value="{{ $enrolmentFormHasErrors ? old('course_title') : '' }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Provider</label>
                                            <input type="text" name="tafe_provider" class="form-control"
                                                value="{{ $enrolmentFormHasErrors ? old('tafe_provider') : '' }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="3" required>{{ $enrolmentFormHasErrors ? old('description') : '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Enrolment link</label>
                                            <input type="url" name="enrol_link" class="form-control"
                                                value="{{ $enrolmentFormHasErrors ? old('enrol_link') : '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Profile summary</label>
                                            <textarea name="enrolment_profile_bio" class="form-control" rows="3">{{ $enrolmentFormHasErrors ? old('enrolment_profile_bio') : '' }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Profile picture URL</label>
                                            <input type="url" name="enrolment_picture_url" class="form-control"
                                                value="{{ $enrolmentFormHasErrors ? old('enrolment_picture_url') : '' }}">
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label mb-0">Details</label>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-add-profile-detail="enrolment-profile-details-fields">Add
                                                    detail</button>
                                            </div>
                                            <div class="d-grid gap-2 mt-2" id="enrolment-profile-details-fields"
                                                data-input-prefix="enrolment_profile_details"
                                                data-next-index="{{ count($enrolmentProfileDetails) }}">
                                                @foreach ($enrolmentProfileDetails as $index => $detail)
                                                    <div class="row g-2 detail-row">
                                                        <div class="col">
                                                            <input type="text"
                                                                name="enrolment_profile_details[{{ $index }}]"
                                                                class="form-control" value="{{ $detail }}"
                                                                placeholder="Detail">
                                                        </div>
                                                        <div class="col-auto text-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button class="btn btn-danger" type="submit">Save enrolment listing</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade {{ $headerTab === 'manage' ? 'show active' : '' }}" id="discover-manage-pane"
                    role="tabpanel" aria-labelledby="discover-manage-tab" tabindex="0"
                    data-x-show="activeTab === 'manage'" data-x-bind:class="activeTab === 'manage' ? 'show active' : ''">
                    <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the Manage tab to edit or
                        remove your listings.</p>
                </div>
            @endcan
        </div>
    </div>

    @include('platform.partials.search-bar', [
        'searchQuery' => $searchQuery ?? '',
        'selectedDetails' => $selectedDetails ?? [],
        'detailOptions' => $detailOptions ?? [],
        'label' => 'Search listings',
        'placeholder' => 'Search jobs, courses, providers, or descriptions',
        'buttonLabel' => 'Discover Listings',
        'inputId' => 'discover-search',
    ])

    <div class="row g-4 {{ $headerTab === 'manage' ? 'd-none' : '' }}" id="discover-public-list">
        <div class="col-12 col-xl-6">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100" id="job-listings-list">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h4 mb-0">Career Opportunities</h3>
                    <span class="badge text-bg-danger">Jobs</span>
                </div>

                <div class="d-grid gap-3">
                    @forelse ($jobListings as $listing)
                        <div class="border rounded-4 p-3">
                            @if ($listing->profile?->picture_url)
                                <img src="{{ $listing->profile->picture_url }}" alt="{{ $listing->title }} cover"
                                    class="nmtafe-hero rounded-3 mb-3">
                            @endif
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $listing->title }}</div>
                                    <div class="small text-secondary">{{ $listing->company }}</div>
                                </div>
                                <div class="small text-secondary text-end">{{ $listing->creator?->name ?? 'Admin' }}</div>
                            </div>
                            <p class="text-secondary mb-2">{{ $listing->description }}</p>
                            @if ($listing->profile?->bio)
                                <p class="text-secondary small mb-2">{{ $listing->profile->bio }}</p>
                            @endif
                            @if (!empty($listing->profile?->details))
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach ($listing->profile->details as $detail)
                                        <span
                                            class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if ($listing->apply_link)
                                <a href="{{ route('job-listings.apply', $listing) }}"
                                    class="btn btn-outline-danger btn-sm" target="_blank" rel="noreferrer">Apply now</a>
                            @endif
                        </div>
                    @empty
                        <div class="text-secondary">
                            {{ !empty($searchQuery) ? 'No job listings match your search.' : 'No job listings are available yet.' }}
                        </div>
                    @endforelse
                </div>

                @if ($jobListings->hasPages())
                    <x-compact-pagination route-name="discover" page-name="jobsPage" :current-page="$jobListings->currentPage()" :total-pages="$jobListings->lastPage()"
                        :query-params="request()->query()" anchor="job-listings-list" />
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100" id="enrolment-listings-list">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h4 mb-0">Professional Development</h3>
                    <span class="badge text-bg-danger">Learning</span>
                </div>

                <div class="d-grid gap-3">
                    @forelse ($enrolmentListings as $listing)
                        <div class="border rounded-4 p-3">
                            @if ($listing->profile?->picture_url)
                                <img src="{{ $listing->profile->picture_url }}" alt="{{ $listing->course_title }} cover"
                                    class="nmtafe-hero rounded-3 mb-3">
                            @endif
                            <div class="fw-semibold">{{ $listing->course_title }}</div>
                            <div class="small text-secondary mb-2">{{ $listing->tafe_provider }}</div>
                            <p class="text-secondary mb-2">{{ $listing->description }}</p>
                            @if ($listing->profile?->bio)
                                <p class="text-secondary small mb-2">{{ $listing->profile->bio }}</p>
                            @endif
                            @if (!empty($listing->profile?->details))
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @foreach ($listing->profile->details as $detail)
                                        <span
                                            class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="small text-secondary mb-2">Published by {{ $listing->creator?->name ?? 'Admin' }}
                            </div>
                            @if ($listing->enrol_link)
                                <a href="{{ route('enrolment-listings.enrol', $listing) }}"
                                    class="btn btn-outline-danger btn-sm" target="_blank" rel="noreferrer">Enrol now</a>
                            @endif
                        </div>
                    @empty
                        <div class="text-secondary">
                            {{ !empty($searchQuery) ? 'No enrolment listings match your search.' : 'No enrolment listings are available yet.' }}
                        </div>
                    @endforelse
                </div>

                @if ($enrolmentListings->hasPages())
                    <x-compact-pagination route-name="discover" page-name="enrolmentsPage" :current-page="$enrolmentListings->currentPage()"
                        :total-pages="$enrolmentListings->lastPage()" :query-params="request()->query()" anchor="enrolment-listings-list" />
                @endif
            </div>
        </div>
    </div>

    @can('create', \App\Models\JobListing::class)
        <div class="row g-4 {{ $headerTab === 'manage' ? '' : 'd-none' }}" id="discover-manage-list">
            <div class="col-12 col-xl-6">
                <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h4 mb-0">Manage job listings</h3>
                        <span class="badge text-bg-danger">Jobs</span>
                    </div>
                    @if (session('status_job_listing_manage'))
                        <div class="alert alert-success py-2 small mb-3">{{ session('status_job_listing_manage') }}</div>
                    @endif

                    <div class="d-grid gap-3">
                        @forelse ($manageableJobListings as $listing)
                            <div class="border rounded-4 p-3">
                                @if ($listing->profile?->picture_url)
                                    <img src="{{ $listing->profile->picture_url }}" alt="{{ $listing->title }} cover"
                                        class="nmtafe-hero rounded-3 mb-3">
                                @endif
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <div>
                                        <div class="fw-semibold">{{ $listing->title }}</div>
                                        <div class="small text-secondary">{{ $listing->company }}</div>
                                    </div>
                                    <div class="small text-secondary text-end">{{ $listing->creator?->name ?? 'Admin' }}</div>
                                </div>
                                <p class="text-secondary mb-2">{{ $listing->description }}</p>
                                @if ($listing->profile?->bio)
                                    <p class="text-secondary small mb-2">{{ $listing->profile->bio }}</p>
                                @endif
                                @if (!empty($listing->profile?->details))
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach ($listing->profile->details as $detail)
                                            <span
                                                class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($listing->apply_link)
                                    <a href="{{ $listing->apply_link }}" class="btn btn-outline-danger btn-sm"
                                        target="_blank" rel="noreferrer">Apply now</a>
                                @endif
                                <div class="d-flex gap-2 mt-3">
                                    <form method="POST" action="{{ route('job-listings.destroy', $listing) }}"
                                        class="m-0">
                                        <button type="button" class="btn btn-light btn-sm border"
                                            data-toggle-target="job-listing-edit-{{ $listing->id }}"
                                            data-toggle-open-text="Edit" data-toggle-close-text="Close"
                                            aria-expanded="false">
                                            Edit
                                        </button>
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <div class="collapse" id="job-listing-edit-{{ $listing->id }}">
                                <div class="nmtafe-card bg-white rounded-4 p-4 border">
                                    <form method="POST" action="{{ route('job-listings.update', $listing) }}"
                                        class="row g-2">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        @method('PATCH')
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Title</label>
                                            <input type="text" name="title" class="form-control"
                                                value="{{ $listing->title }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Company</label>
                                            <input type="text" name="company" class="form-control"
                                                value="{{ $listing->company }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Description</label>
                                            <textarea name="description" class="form-control" rows="3" required>{{ $listing->description }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Apply link</label>
                                            <input type="url" name="apply_link" class="form-control"
                                                value="{{ $listing->apply_link }}">
                                        </div>
                                        @php($jobEditProfileDetails = $listing->profile?->details ?? [])
                                        @if (empty($jobEditProfileDetails))
                                            @php($jobEditProfileDetails = [''])
                                        @endif
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Profile summary</label>
                                            <textarea name="job_profile_bio" class="form-control" rows="3">{{ $listing->profile?->bio }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Profile picture URL</label>
                                            <input type="url" name="job_picture_url" class="form-control"
                                                value="{{ $listing->profile?->picture_url }}">
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label fw-semibold small mb-0">Details</label>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-add-profile-detail="job-listing-details-edit-{{ $listing->id }}">Add
                                                    detail</button>
                                            </div>
                                            <div class="d-grid gap-2 mt-2" id="job-listing-details-edit-{{ $listing->id }}"
                                                data-input-prefix="job_profile_details"
                                                data-next-index="{{ count($jobEditProfileDetails) }}">
                                                @foreach ($jobEditProfileDetails as $index => $detail)
                                                    <div class="row g-2 detail-row">
                                                        <div class="col">
                                                            <input type="text"
                                                                name="job_profile_details[{{ $index }}]"
                                                                class="form-control" value="{{ $detail }}"
                                                                placeholder="Detail">
                                                        </div>
                                                        <div class="col-auto text-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex align-items-center justify-content-between">
                                            <div class="small text-secondary">Published by
                                                {{ $listing->creator?->name ?? 'Admin' }}</div>
                                            <button class="btn btn-danger btn-sm" type="submit">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary">
                                {{ !empty($searchQuery) ? 'No managed job listings match your search.' : 'You have not created any job listings yet.' }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="h4 mb-0">Manage enrolment listings</h3>
                        <span class="badge text-bg-danger">Learning</span>
                    </div>
                    @if (session('status_enrolment_listing_manage'))
                        <div class="alert alert-success py-2 small mb-3">{{ session('status_enrolment_listing_manage') }}
                        </div>
                    @endif

                    <div class="d-grid gap-3">
                        @forelse ($manageableEnrolmentListings as $listing)
                            <div class="border rounded-4 p-3">
                                @if ($listing->profile?->picture_url)
                                    <img src="{{ $listing->profile->picture_url }}" alt="{{ $listing->course_title }} cover"
                                        class="nmtafe-hero rounded-3 mb-3">
                                @endif
                                <div class="fw-semibold">{{ $listing->course_title }}</div>
                                <div class="small text-secondary mb-2">{{ $listing->tafe_provider }}</div>
                                <p class="text-secondary mb-2">{{ $listing->description }}</p>
                                @if ($listing->profile?->bio)
                                    <p class="text-secondary small mb-2">{{ $listing->profile->bio }}</p>
                                @endif
                                @if (!empty($listing->profile?->details))
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach ($listing->profile->details as $detail)
                                            <span
                                                class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="small text-secondary">Published by {{ $listing->creator?->name ?? 'Admin' }}</div>
                                <div class="d-flex gap-2 mt-3">
                                    <form method="POST" action="{{ route('enrolment-listings.destroy', $listing) }}"
                                        class="m-0">
                                        <button type="button" class="btn btn-light btn-sm border"
                                            data-toggle-target="enrolment-listing-edit-{{ $listing->id }}"
                                            data-toggle-open-text="Edit" data-toggle-close-text="Close"
                                            aria-expanded="false">
                                            Edit
                                        </button>
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                            <div class="collapse" id="enrolment-listing-edit-{{ $listing->id }}">
                                <div class="nmtafe-card bg-white rounded-4 p-4 border">
                                    <form method="POST" action="{{ route('enrolment-listings.update', $listing) }}"
                                        class="row g-2">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        @method('PATCH')
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Course title</label>
                                            <input type="text" name="course_title" class="form-control"
                                                value="{{ $listing->course_title }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Provider</label>
                                            <input type="text" name="tafe_provider" class="form-control"
                                                value="{{ $listing->tafe_provider }}" required>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Description</label>
                                            <textarea name="description" class="form-control" rows="3" required>{{ $listing->description }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Enrolment link</label>
                                            <input type="url" name="enrol_link" class="form-control"
                                                value="{{ $listing->enrol_link }}">
                                        </div>
                                        @php($enrolmentEditProfileDetails = $listing->profile?->details ?? [])
                                        @if (empty($enrolmentEditProfileDetails))
                                            @php($enrolmentEditProfileDetails = [''])
                                        @endif
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Profile summary</label>
                                            <textarea name="enrolment_profile_bio" class="form-control" rows="3">{{ $listing->profile?->bio }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold small">Profile picture URL</label>
                                            <input type="url" name="enrolment_picture_url" class="form-control"
                                                value="{{ $listing->profile?->picture_url }}">
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <label class="form-label fw-semibold small mb-0">Details</label>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-add-profile-detail="enrolment-listing-details-edit-{{ $listing->id }}">Add
                                                    detail</button>
                                            </div>
                                            <div class="d-grid gap-2 mt-2"
                                                id="enrolment-listing-details-edit-{{ $listing->id }}"
                                                data-input-prefix="enrolment_profile_details"
                                                data-next-index="{{ count($enrolmentEditProfileDetails) }}">
                                                @foreach ($enrolmentEditProfileDetails as $index => $detail)
                                                    <div class="row g-2 detail-row">
                                                        <div class="col">
                                                            <input type="text"
                                                                name="enrolment_profile_details[{{ $index }}]"
                                                                class="form-control" value="{{ $detail }}"
                                                                placeholder="Detail">
                                                        </div>
                                                        <div class="col-auto text-end">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex align-items-center justify-content-between">
                                            <div class="small text-secondary">Published by
                                                {{ $listing->creator?->name ?? 'Admin' }}</div>
                                            <button class="btn btn-danger btn-sm" type="submit">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary">
                                {{ !empty($searchQuery) ? 'No managed enrolment listings match your search.' : 'You have not created any enrolment listings yet.' }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <script>
        (() => {
            const publicList = document.getElementById('discover-public-list');
            const manageList = document.getElementById('discover-manage-list');
            const tabButtons = document.querySelectorAll('#discoverHeaderTabs [data-bs-toggle="tab"]');

            const initCollapseToggles = () => {
                document.querySelectorAll('[data-toggle-target]').forEach((button) => {
                    const targetId = button.getAttribute('data-toggle-target');
                    const target = document.getElementById(targetId);
                    if (!target) return;

                    const openText = button.getAttribute('data-toggle-open-text');
                    const closeText = button.getAttribute('data-toggle-close-text');

                    const setButtonState = (isOpen) => {
                        button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                        if (openText && closeText) {
                            button.textContent = isOpen ? closeText : openText;
                        }
                    };

                    setButtonState(target.classList.contains('show'));

                    button.addEventListener('click', (event) => {
                        event.preventDefault();
                        const isOpen = target.classList.contains('show');
                        target.classList.toggle('show', !isOpen);
                        setButtonState(!isOpen);
                    });
                });
            };

            initCollapseToggles();

            document.querySelectorAll('[data-add-profile-detail]').forEach((button) => {
                const targetId = button.getAttribute('data-add-profile-detail');
                const container = document.getElementById(targetId);

                if (!container) {
                    return;
                }

                button.addEventListener('click', (event) => {
                    event.preventDefault();

                    const inputPrefix = container.getAttribute('data-input-prefix');
                    const nextIndex = Number(container.getAttribute('data-next-index') || container
                        .children.length);

                    const row = document.createElement('div');
                    row.className = 'row g-2 detail-row';
                    row.innerHTML = `
                        <div class="col">
                            <input type="text" name="${inputPrefix}[${nextIndex}]" class="form-control" placeholder="Detail">
                        </div>
                        <div class="col-auto text-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                        </div>
                    `;

                    container.appendChild(row);
                    container.setAttribute('data-next-index', String(nextIndex + 1));
                });
            });

            document.addEventListener('click', (event) => {
                const removeButton = event.target.closest('.remove-detail');
                if (!removeButton) {
                    return;
                }

                const detailRow = removeButton.closest('.detail-row');
                if (detailRow) {
                    detailRow.remove();
                }
            });

            if (!publicList || !manageList || !tabButtons.length) {
                return;
            }

            const setListVisibility = (targetId) => {
                const showManage = targetId === '#discover-manage-pane';
                publicList.classList.toggle('d-none', showManage);
                manageList.classList.toggle('d-none', !showManage);
            };

            tabButtons.forEach((button) => {
                button.addEventListener('shown.bs.tab', (event) => {
                    setListVisibility(event.target.getAttribute('data-bs-target'));
                });
            });

            const initialTab = @json($headerTab);
            setListVisibility(`#discover-${initialTab}-pane`);
        })();
    </script>
@endsection
