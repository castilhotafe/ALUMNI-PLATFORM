@extends('layouts.platform')

@section('content')
    @php($headerTab = 'overview')
    @if ($errors->getBag('project')->any() || session('status_project'))
        @php($headerTab = 'create')
    @endif

    @php($collaboratorInputs = old('collaborators', [['name' => '', 'role' => '']]))

    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border" data-x-data="sectionTabs('{{ $headerTab }}')">
        <div class="d-md-flex align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Projects</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Collaborative workspaces and shared outcomes.</h2>
                <p class="lead text-secondary mb-0">Review projects and share new work with the community.</p>
            </div>

            <ul class="nav nav-pills mt-4 mt-md-0 p-1 rounded-3 flex-nowrap" id="projectHeaderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border" id="project-overview-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#project-overview-pane" data-x-on:click="activate('overview')"
                        data-x-bind:class="activeTab === 'overview' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="project-overview-pane"
                        data-x-bind:aria-selected="activeTab === 'overview'">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="project-create-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#project-create-pane" data-x-on:click="activate('create')"
                        data-x-bind:class="activeTab === 'create' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="project-create-pane" data-x-bind:aria-selected="activeTab === 'create'">
                        <i class="bi bi-kanban me-1"></i> Create Project
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="project-manage-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#project-manage-pane" data-x-on:click="activate('manage')"
                        data-x-bind:class="activeTab === 'manage' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="project-manage-pane" data-x-bind:aria-selected="activeTab === 'manage'">
                        <i class="bi bi-sliders me-1"></i> Manage Projects
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="projectHeaderTabsContent">
            <div class="tab-pane fade {{ $headerTab === 'overview' ? 'show active' : '' }}" id="project-overview-pane"
                role="tabpanel" aria-labelledby="project-overview-tab" tabindex="0" data-x-show="activeTab === 'overview'"
                data-x-bind:class="activeTab === 'overview' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the Create tab to publish a new
                    project or add collaborators.</p>
            </div>

            <div class="tab-pane fade {{ $headerTab === 'create' ? 'show active' : '' }}" id="project-create-pane"
                role="tabpanel" aria-labelledby="project-create-tab" tabindex="0" data-x-show="activeTab === 'create'"
                data-x-bind:class="activeTab === 'create' ? 'show active' : ''">
                <div class="pt-4 border-top">
                    @if (session('status_project'))
                        <div class="alert alert-success py-2 small mb-3">{{ session('status_project') }}</div>
                    @endif
                    @if ($errors->getBag('project')->any())
                        <div class="alert alert-danger py-2 small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->getBag('project')->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('projects.store') }}" class="row g-3">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Project title</label>
                            <input type="text" name="title" class="form-control" maxlength="255"
                                placeholder="Give the project a clear name" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Repository URL</label>
                            <input type="url" name="repo_url" class="form-control" maxlength="255"
                                placeholder="https://github.com/..." value="{{ old('repo_url') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Visibility</label>
                            <select name="visibility" class="form-select">
                                <option value="public" @selected(old('visibility') === 'public')>Public - Everyone can see</option>
                                <option value="private" @selected(old('visibility') === 'private')>Private - Only collaborators</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe the project scope and goals"
                                required>{{ old('description') }}</textarea>
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
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label fw-semibold mb-0">Details</label>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-add-detail="project-details-fields">Add detail</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="project-details-fields"
                                data-next-index="{{ count(old('profile_details', [])) }}">
                                @foreach (old('profile_details', []) as $index => $detail)
                                    <div class="row g-2 align-items-center detail-row">
                                        <div class="col-md-10">
                                            <input type="text" name="profile_details[{{ $index }}]"
                                                class="form-control" value="{{ $detail }}" placeholder="Detail">
                                        </div>
                                        <div class="col-md-2 text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label fw-semibold mb-0">Collaborators</label>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="add-collaborator-btn"
                                    data-add-collaborator="collaborator-fields">Add collaborator</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="collaborator-fields"
                                data-next-index="{{ count($collaboratorInputs) }}">
                                @foreach ($collaboratorInputs as $index => $collaborator)
                                    <div class="row g-2 collaborator-row">
                                        <div class="col-md-7">
                                            <input type="text" name="collaborators[{{ $index }}][name]"
                                                class="form-control" placeholder="Collaborator name"
                                                value="{{ $collaborator['name'] ?? '' }}">
                                        </div>
                                        <div class="col">
                                            <input type="text" name="collaborators[{{ $index }}][role]"
                                                class="form-control" placeholder="Role (optional)"
                                                value="{{ $collaborator['role'] ?? '' }}">
                                        </div>
                                        <div class="col-auto text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary remove-collaborator">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Add names for external collaborators. Your name is added automatically.
                            </div>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-danger px-4" type="submit">Publish project</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade {{ $headerTab === 'manage' ? 'show active' : '' }}" id="project-manage-pane"
                role="tabpanel" aria-labelledby="project-manage-tab" tabindex="0" data-x-show="activeTab === 'manage'"
                data-x-bind:class="activeTab === 'manage' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the Manage tab to edit or
                    remove your projects.</p>
            </div>
        </div>
    </div>

    @include('components.search-bar', [
        'searchQuery' => $searchQuery ?? '',
        'selectedDetails' => $selectedDetails ?? [],
        'detailOptions' => $detailOptions ?? [],
        'label' => 'Search projects',
        'placeholder' => 'Search by title, description, collaborator, or detail',
        'buttonLabel' => 'Find Projects',
        'inputId' => 'projects-search',
    ])

    <div class="nmtafe-masonry {{ $headerTab === 'manage' ? 'd-none' : '' }}" id="project-public-list">
        @forelse ($projects as $project)
            <div class="nmtafe-masonry-item">
                <div class="nmtafe-card bg-white rounded-4 p-4">
                    @if ($project->profile?->picture_url)
                        <img src="{{ $project->profile->picture_url }}" alt="{{ $project->title }} cover"
                            class="nmtafe-hero rounded-3 mb-3">
                    @endif
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-semibold">{{ $project->title }}</div>
                        <span class="badge text-bg-light">{{ $project->collaborators_count }} Collaborators</span>
                    </div>
                    <p class="text-secondary">{{ $project->description }}</p>
                    @if ($project->profile?->bio)
                        <p class="text-secondary small mb-2">{{ $project->profile->bio }}</p>
                    @endif
                    @if (!empty($project->profile?->details))
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($project->profile->details as $detail)
                                <span
                                    class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 justify-content-between">
                        <div class="small text-secondary">{{ $project->repo_url ?? 'Repository link not shared' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 w-100" style="column-span: all;">
                <div class="alert alert-light border">
                    {{ !empty($searchQuery) ? 'No projects match your search.' : 'Projects will appear here once alumni seed content or create them.' }}
                </div>
            </div>
        @endforelse
    </div>

    <div class="nmtafe-masonry {{ $headerTab === 'manage' ? '' : 'd-none' }}" id="project-manage-list">
        @forelse ($userProjects as $project)
            <div class="nmtafe-masonry-item">
                <div class="nmtafe-card bg-white rounded-4 p-4">
                    @if ($project->profile?->picture_url)
                        <img src="{{ $project->profile->picture_url }}" alt="{{ $project->title }} cover"
                            class="nmtafe-hero rounded-3 mb-3">
                    @endif
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-semibold">{{ $project->title }}</div>
                        <span class="badge text-bg-light">{{ $project->collaborators_count }} Collaborators</span>
                    </div>
                    <p class="text-secondary">{{ $project->description }}</p>
                    @if ($project->profile?->bio)
                        <p class="text-secondary small mb-2">{{ $project->profile->bio }}</p>
                    @endif
                    @if (!empty($project->profile?->details))
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($project->profile->details as $detail)
                                <span
                                    class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div class="small text-secondary">{{ $project->repo_url ?? 'Repository link not shared' }}</div>
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('projects.destroy', $project) }}" class="m-0">
                                <button type="button" class="btn btn-light btn-sm border"
                                    data-toggle-target="project-edit-{{ $project->id }}" data-toggle-open-text="Edit"
                                    data-toggle-close-text="Close" aria-expanded="false">
                                    Edit
                                </button>
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            <div class="nmtafe-masonry-span collapse" id="project-edit-{{ $project->id }}">
                <div class="nmtafe-card bg-white rounded-4 p-4 border">
                    <form method="POST" action="{{ route('projects.update', $project) }}" class="row g-2">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @method('PATCH')
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Project title</label>
                            <input type="text" name="title" class="form-control" value="{{ $project->title }}"
                                required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description</label>
                            <textarea name="description" class="form-control" rows="3" required>{{ $project->description }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Profile Summary</label>
                            <textarea name="profile_bio" class="form-control" rows="3" placeholder="Share a short profile summary">{{ $project->profile?->bio }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Profile picture URL</label>
                            <input type="url" name="picture_url" class="form-control"
                                value="{{ $project->profile?->picture_url }}"
                                placeholder="https://example.com/photo.jpg">
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label fw-semibold small mb-0">Details</label>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-add-detail="project-details-edit-{{ $project->id }}">Add detail</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="project-details-edit-{{ $project->id }}"
                                data-next-index="{{ count($project->profile?->details ?? []) }}">
                                @forelse ($project->profile?->details ?? [] as $index => $detail)
                                    <div class="row g-2 detail-row">
                                        <div class="col">
                                            <input type="text" name="profile_details[{{ $index }}]"
                                                class="form-control" value="{{ $detail }}" placeholder="Detail">
                                        </div>
                                        <div class="col-auto text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between">
                                <label class="form-label fw-semibold small mb-0">Collaborators</label>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-add-collaborator="collaborators-edit-{{ $project->id }}">Add
                                    collaborator</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="collaborators-edit-{{ $project->id }}"
                                data-next-index="{{ $project->collaborators->count() }}">
                                @forelse ($project->collaborators->values() as $index => $collaborator)
                                    <div class="row g-2 collaborator-row">
                                        <div class="col-md-7">
                                            <input type="text" name="collaborators[{{ $index }}][name]"
                                                class="form-control" value="{{ $collaborator->name }}"
                                                placeholder="Collaborator name">
                                        </div>
                                        <div class="col">
                                            <input type="text" name="collaborators[{{ $index }}][role]"
                                                class="form-control" value="{{ $collaborator->role }}"
                                                placeholder="Role (optional)">
                                        </div>
                                        <div class="col-auto text-end">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary remove-collaborator">Remove</button>
                                        </div>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Repository URL</label>
                            <input type="url" name="repo_url" class="form-control" value="{{ $project->repo_url }}"
                                placeholder="https://github.com/...">
                        </div>
                        <div class="col-12 d-flex align-items-center justify-content-between">
                            <select name="visibility" class="form-select w-auto">
                                <option value="public" @selected($project->visibility === 'public')>Public</option>
                                <option value="private" @selected($project->visibility === 'private')>Private</option>
                            </select>
                            <button class="btn btn-danger btn-sm" type="submit">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border">
                    {{ !empty($searchQuery) ? 'No managed projects match your search.' : 'You have not created any projects yet.' }}
                </div>
            </div>
        @endforelse
    </div>

    <script>
        (() => {
            const publicList = document.getElementById('project-public-list');
            const manageList = document.getElementById('project-manage-list');
            const tabButtons = document.querySelectorAll('#projectHeaderTabs button[role="tab"]');
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

            const addCollaboratorRow = (container) => {
                const index = Number(container.dataset.nextIndex || 0);
                container.dataset.nextIndex = String(index + 1);

                const row = document.createElement('div');
                row.className = 'row g-2 collaborator-row';
                row.innerHTML = `
                    <div class="col-md-7">
                        <input type="text" name="collaborators[${index}][name]" class="form-control" placeholder="Collaborator name">
                    </div>
                    <div class="col">
                        <input type="text" name="collaborators[${index}][role]" class="form-control" placeholder="Role (optional)">
                    </div>
                    <div class="col-auto text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary remove-collaborator">Remove</button>
                    </div>
                `;

                container.appendChild(row);
            };

            const addDetailRow = (container) => {
                const index = Number(container.dataset.nextIndex || 0);
                container.dataset.nextIndex = String(index + 1);

                const row = document.createElement('div');
                row.className = 'row g-2 detail-row';
                row.innerHTML = `
                    <div class="col">
                        <input type="text" name="profile_details[${index}]" class="form-control" placeholder="Detail">
                    </div>
                    <div class="col-auto text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary remove-detail">Remove</button>
                    </div>
                `;

                container.appendChild(row);
            };

            document.addEventListener('click', (event) => {
                const addBtn = event.target.closest('[data-add-collaborator]');
                if (addBtn) {
                    event.preventDefault();
                    const targetId = addBtn.getAttribute('data-add-collaborator');
                    const container = document.getElementById(targetId);
                    if (container) {
                        addCollaboratorRow(container);
                    }
                    return;
                }

                const addDetailBtn = event.target.closest('[data-add-detail]');
                if (addDetailBtn) {
                    event.preventDefault();
                    const targetId = addDetailBtn.getAttribute('data-add-detail');
                    const container = document.getElementById(targetId);
                    if (container) {
                        addDetailRow(container);
                    }
                    return;
                }

                if (event.target.classList.contains('remove-collaborator')) {
                    event.preventDefault();
                    event.target.closest('.collaborator-row')?.remove();
                    return;
                }

                if (event.target.classList.contains('remove-detail')) {
                    event.preventDefault();
                    event.target.closest('.detail-row')?.remove();
                }
            });

            if (!publicList || !manageList || !tabButtons.length) {
                return;
            }

            const setListVisibility = (targetId) => {
                const showManage = targetId === '#project-manage-pane';
                publicList.classList.toggle('d-none', showManage);
                manageList.classList.toggle('d-none', !showManage);
            };

            tabButtons.forEach((button) => {
                button.addEventListener('shown.bs.tab', (event) => {
                    setListVisibility(event.target.getAttribute('data-bs-target'));
                });
            });

            initCollapseToggles();

            const initialTab = @json($headerTab);
            setListVisibility(`#project-${initialTab}-pane`);
        })();
    </script>
@endsection
