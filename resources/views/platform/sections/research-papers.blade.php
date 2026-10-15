@extends('layouts.platform')

@section('content')
    @php($headerTab = 'overview')
    @if ($errors->getBag('paper')->any() || session('status_paper'))
        @php($headerTab = 'create')
    @endif

    @php($authorInputs = old('authors', [['name' => '', 'order' => 1]]))

    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border" data-x-data="sectionTabs('{{ $headerTab }}')">
        <div class="d-md-flex align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Research Papers</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Knowledge sharing and publication tracking.</h2>
                <p class="lead text-secondary mb-0">Review recent publications and add new research when ready.</p>
            </div>

            <ul class="nav nav-pills mt-4 mt-md-0 p-1 rounded-3 flex-nowrap" id="paperHeaderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border" id="paper-overview-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#paper-overview-pane" data-x-on:click="activate('overview')"
                        data-x-bind:class="activeTab === 'overview' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="paper-overview-pane"
                        data-x-bind:aria-selected="activeTab === 'overview'">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="paper-create-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#paper-create-pane" data-x-on:click="activate('create')"
                        data-x-bind:class="activeTab === 'create' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="paper-create-pane" data-x-bind:aria-selected="activeTab === 'create'">
                        <i class="bi bi-journal-text me-1"></i> Create Paper
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="paper-manage-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#paper-manage-pane" data-x-on:click="activate('manage')"
                        data-x-bind:class="activeTab === 'manage' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="paper-manage-pane" data-x-bind:aria-selected="activeTab === 'manage'">
                        <i class="bi bi-sliders me-1"></i> Manage Papers
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="paperHeaderTabsContent">
            <div class="tab-pane fade {{ $headerTab === 'overview' ? 'show active' : '' }}" id="paper-overview-pane"
                role="tabpanel" aria-labelledby="paper-overview-tab" tabindex="0" data-x-show="activeTab === 'overview'"
                data-x-bind:class="activeTab === 'overview' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the Create tab to publish a new
                    paper with authors and links.</p>
            </div>

            <div class="tab-pane fade {{ $headerTab === 'create' ? 'show active' : '' }}" id="paper-create-pane"
                role="tabpanel" aria-labelledby="paper-create-tab" tabindex="0" data-x-show="activeTab === 'create'"
                data-x-bind:class="activeTab === 'create' ? 'show active' : ''">
                <div class="pt-4 border-top">
                    @if (session('status_paper'))
                        <div class="alert alert-success py-2 small mb-3">{{ session('status_paper') }}</div>
                    @endif
                    @if ($errors->getBag('paper')->any())
                        <div class="alert alert-danger py-2 small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->getBag('paper')->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('papers.store') }}" class="row g-3">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Paper title</label>
                            <input type="text" name="title" class="form-control" maxlength="255"
                                placeholder="Paper title" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">DOI</label>
                            <input type="text" name="doi" class="form-control" maxlength="255"
                                placeholder="10.1234/example" value="{{ old('doi') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">PDF URL</label>
                            <input type="url" name="pdf_url" class="form-control" maxlength="255"
                                placeholder="https://..." value="{{ old('pdf_url') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Visibility</label>
                            <select name="visibility" class="form-select">
                                <option value="public" @selected(old('visibility') === 'public')>Public - Everyone can see</option>
                                <option value="private" @selected(old('visibility') === 'private')>Private - Only authors</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Abstract</label>
                            <textarea name="abstract" class="form-control" rows="3" placeholder="Short abstract" required>{{ old('abstract') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Profile Summary</label>
                            <textarea name="profile_bio" class="form-control" rows="3" placeholder="Share a short profile summary">{{ old('profile_bio') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Profile picture URL</label>
                            <input type="url" name="picture_url" class="form-control"
                                placeholder="https://example.com/photo.jpg" value="{{ old('picture_url') }}">
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <label class="form-label fw-semibold mb-0">Details</label>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-add-detail="paper-details-fields">Add detail</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="paper-details-fields"
                                data-next-index="{{ count(old('profile_details', [])) }}">
                                @foreach (old('profile_details', []) as $index => $detail)
                                    <div class="row g-2 align-items-center detail-row">
                                        <div class="col">
                                            <input type="text" name="profile_details[{{ $index }}]"
                                                class="form-control" value="{{ $detail }}" placeholder="Detail">
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
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label fw-semibold mb-0">Authors</label>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="add-author-btn"
                                    data-add-author="author-fields">Add author</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="author-fields"
                                data-next-index="{{ count($authorInputs) }}">
                                @foreach ($authorInputs as $index => $author)
                                    <div class="row g-2 align-items-center author-row">
                                        <div class="col-md-7">
                                            <input type="text" name="authors[{{ $index }}][name]"
                                                class="form-control" placeholder="Author name"
                                                value="{{ $author['name'] ?? '' }}">
                                        </div>
                                        <div class="col">
                                            <input type="number" min="1"
                                                name="authors[{{ $index }}][order]" class="form-control"
                                                placeholder="Order" value="{{ $author['order'] ?? $index + 1 }}">
                                        </div>
                                        <div class="col-auto text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary remove-author">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">List all contributors in the preferred order. Your name is added
                                automatically.</div>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-danger px-4" type="submit">Publish paper</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade {{ $headerTab === 'manage' ? 'show active' : '' }}" id="paper-manage-pane"
                role="tabpanel" aria-labelledby="paper-manage-tab" tabindex="0" data-x-show="activeTab === 'manage'"
                data-x-bind:class="activeTab === 'manage' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the Manage tab to edit or
                    remove your papers.</p>
            </div>
        </div>
    </div>

    @include('components.search-bar', [
        'searchQuery' => $searchQuery ?? '',
        'selectedDetails' => $selectedDetails ?? [],
        'detailOptions' => $detailOptions ?? [],
        'label' => 'Search papers',
        'placeholder' => 'Search by title, abstract, DOI, author, or detail',
        'buttonLabel' => 'Find Papers',
        'inputId' => 'papers-search',
    ])

    <div class="nmtafe-masonry {{ $headerTab === 'manage' ? 'd-none' : '' }}" id="paper-public-list">
        @forelse ($papers as $paper)
            <div class="nmtafe-masonry-item">
                <div class="nmtafe-card bg-white rounded-4 p-4">
                    @if ($paper->profile?->picture_url)
                        <img src="{{ $paper->profile->picture_url }}" alt="{{ $paper->title }} cover"
                            class="nmtafe-hero rounded-3 mb-3">
                    @endif
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-semibold">{{ $paper->title }}</div>
                        <span class="badge text-bg-light">{{ $paper->authors_count }} Authors</span>
                    </div>
                    <p class="text-secondary">{{ $paper->abstract }}</p>
                    @if ($paper->profile?->bio)
                        <p class="text-secondary small mb-2">{{ $paper->profile->bio }}</p>
                    @endif
                    @if (!empty($paper->profile?->details))
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($paper->profile->details as $detail)
                                <span
                                    class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="small text-secondary mb-3">
                        <div>DOI: {{ $paper->doi ?? 'Not published' }}</div>
                        <div>PDF: {{ $paper->pdf_url ?? 'Not attached' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100" style="column-span: all;">
                <div class="alert alert-light border">
                    {{ !empty($searchQuery) ? 'No research papers match your search.' : 'Research papers will appear here once users add publications.' }}
                </div>
            </div>
        @endforelse
    </div>
