@extends('layouts.platform')

@section('content')
    @php($headerTab = 'overview')
    @if ($errors->getBag('group')->any() || session('status_group'))
        @php($headerTab = 'create')
    @endif

    @php($selectedProjectIds = old('project_ids', []))
    @php($selectedPaperIds = old('paper_ids', []))
    @php($selectedMemberIds = old('member_ids', [auth()->id()]))

    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border" data-x-data="sectionTabs('{{ $headerTab }}')">
        <div class="d-md-flex align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Networking Hub</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Communities, mentoring, and shared projects.</h2>
                <p class="lead text-secondary mb-0">Create circles around subject areas or interests and manage their
                    membership here.</p>
            </div>

            <ul class="nav nav-pills mt-4 mt-md-0 p-1 rounded-3 flex-nowrap" id="groupHeaderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border" id="group-overview-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#group-overview-pane" data-x-on:click="activate('overview')"
                        data-x-bind:class="activeTab === 'overview' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="group-overview-pane"
                        data-x-bind:aria-selected="activeTab === 'overview'">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="group-create-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#group-create-pane" data-x-on:click="activate('create')"
                        data-x-bind:class="activeTab === 'create' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="group-create-pane" data-x-bind:aria-selected="activeTab === 'create'">
                        <i class="bi bi-people me-1"></i> Create Group
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="group-manage-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#group-manage-pane" data-x-on:click="activate('manage')"
                        data-x-bind:class="activeTab === 'manage' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="group-manage-pane" data-x-bind:aria-selected="activeTab === 'manage'">
                        <i class="bi bi-sliders me-1"></i> Manage Groups
                    </button>
                </li>
            </ul>


        </div>

        <div class="tab-content" id="groupHeaderTabsContent">
            <div class="tab-pane fade {{ $headerTab === 'overview' ? 'show active' : '' }}" id="group-overview-pane"
                role="tabpanel" aria-labelledby="group-overview-tab" tabindex="0" data-x-show="activeTab === 'overview'"
                data-x-bind:class="activeTab === 'overview' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the Create tab to start a new
                    group and connect it to projects, papers, and members.</p>
            </div>

            <div class="tab-pane fade {{ $headerTab === 'create' ? 'show active' : '' }}" id="group-create-pane"
                role="tabpanel" aria-labelledby="group-create-tab" tabindex="0" data-x-show="activeTab === 'create'"
                data-x-bind:class="activeTab === 'create' ? 'show active' : ''">
                <div class="pt-4 border-top">
                    @if (session('status_group'))
                        <div class="alert alert-success py-2 small mb-3">{{ session('status_group') }}</div>
                    @endif
                    @if ($errors->getBag('group')->any())
                        <div class="alert alert-danger py-2 small mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->getBag('group')->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('groups.store') }}" class="row g-3">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Name</label>
                            <input type="text" name="name" class="form-control" maxlength="255"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
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
                                    data-add-detail="group-details-fields">Add detail</button>
                            </div>
                            <div class="d-grid gap-2 mt-2" id="group-details-fields"
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
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Projects</label>
                            <select name="project_ids[]" class="form-select" multiple>
                                @forelse ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(in_array($project->id, $selectedProjectIds))>
                                        {{ $project->title }}</option>
                                @empty
                                    <option disabled>No projects available</option>
                                @endforelse
                            </select>
                            <div class="form-text">Hold Cmd/Ctrl to select multiple projects.</div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Papers</label>
                            <select name="paper_ids[]" class="form-select" multiple>
                                @forelse ($papers as $paper)
                                    <option value="{{ $paper->id }}" @selected(in_array($paper->id, $selectedPaperIds))>{{ $paper->title }}
                                    </option>
                                @empty
                                    <option disabled>No papers available</option>
                                @endforelse
                            </select>
                            <div class="form-text">Attach papers that the group collaborates on.</div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <label class="form-label fw-semibold">Members</label>
                            <select name="member_ids[]" class="form-select" multiple>
                                @forelse ($members as $member)
                                    <option value="{{ $member->id }}" @selected(in_array($member->id, $selectedMemberIds))>{{ $member->name }}
                                    </option>
                                @empty
                                    <option disabled>No members available</option>
                                @endforelse
                            </select>
                            <div class="form-text">Your name is always added as the owner.</div>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-danger px-4" type="submit">Save group</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tab-pane fade {{ $headerTab === 'manage' ? 'show active' : '' }}" id="group-manage-pane"
                role="tabpanel" aria-labelledby="group-manage-tab" tabindex="0" data-x-show="activeTab === 'manage'"
                data-x-bind:class="activeTab === 'manage' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Manage groups you belong to and
                    update details if you are the owner.</p>
            </div>
        </div>
    </div>

    @include('platform.partials.search-bar', [
        'searchQuery' => $searchQuery ?? '',
        'selectedDetails' => $selectedDetails ?? [],
        'detailOptions' => $detailOptions ?? [],
        'label' => 'Search groups',
        'placeholder' => 'Search by group name, people, projects, or papers',
        'buttonLabel' => 'Find Groups',
        'inputId' => 'networking-search',
    ])

    <div class="nmtafe-masonry {{ $headerTab === 'manage' ? 'd-none' : '' }}" id="group-public-list">
        @forelse ($groups as $group)
            <div class="nmtafe-masonry-item">
                <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                    @if ($group->profile?->picture_url)
                        <img src="{{ $group->profile->picture_url }}" alt="{{ $group->name }} cover"
                            class="nmtafe-hero rounded-3 mb-3">
                    @endif
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <div class="fw-semibold h5 mb-1">{{ $group->name }}</div>
                            <div class="small text-secondary">Created by {{ $group->creator?->name ?? 'Alumni' }}</div>
                        </div>
                        <span class="badge text-bg-danger">{{ $group->members_count }} members</span>
                    </div>
                    <p class="text-secondary">{{ $group->description }}</p>
                    @if ($group->profile?->bio)
                        <p class="text-secondary small mb-2">{{ $group->profile->bio }}</p>
                    @endif
                    @if (!empty($group->profile?->details))
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($group->profile->details as $detail)
                                <span
                                    class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="bg-light rounded-3 p-2">
                                <div class="fw-semibold">{{ $group->projects_count }}</div>
                                <div class="small text-secondary">Projects</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded-3 p-2">
                                <div class="fw-semibold">{{ $group->research_papers_count }}</div>
                                <div class="small text-secondary">Papers</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="bg-light rounded-3 p-2">
                                <div class="fw-semibold">{{ $group->members_count }}</div>
                                <div class="small text-secondary">Members</div>
                            </div>
                        </div>
                    </div>
                    @if ($group->projects->isNotEmpty())
                        <div class="mb-2">
                            <div class="small text-uppercase text-secondary">Projects</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($group->projects->take(3) as $project)
                                    <span
                                        class="badge rounded-pill text-bg-light text-danger small">{{ $project->title }}</span>
                                @endforeach
                                @if ($group->projects->count() > 3)
                                    <span
                                        class="badge rounded-pill text-bg-light text-danger small">+{{ $group->projects->count() - 3 }}
                                        more</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    @if ($group->researchPapers->isNotEmpty())
                        <div class="mb-2">
                            <div class="small text-uppercase text-secondary">Papers</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($group->researchPapers->take(3) as $paper)
                                    <span
                                        class="badge rounded-pill text-bg-light text-danger small">{{ $paper->title }}</span>
                                @endforeach
                                @if ($group->researchPapers->count() > 3)
                                    <span
                                        class="badge rounded-pill text-bg-light text-danger small">+{{ $group->researchPapers->count() - 3 }}
                                        more</span>
                                @endif
                            </div>
                        </div>
                    @endif
                    @php($memberNames = $group->members->pluck('name'))
                    @if ($memberNames->isNotEmpty())
                        <div class="small text-secondary mb-3">
                            Members:
                            {{ $memberNames->take(3)->join(', ') }}{{ $memberNames->count() > 3 ? ' +' . ($memberNames->count() - 3) . ' more' : '' }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-light border">
                    {{ !empty($searchQuery) ? 'No groups match your search.' : 'No groups have been created yet.' }}</div>
            </div>
        @endforelse
    </div>

    <div class="nmtafe-masonry {{ $headerTab === 'manage' ? '' : 'd-none' }}" id="group-manage-list">
        @forelse ($memberGroups as $group)
            <div class="nmtafe-masonry-item">
                <div class="nmtafe-card bg-white rounded-4 p-4">
                    @if ($group->profile?->picture_url)
                        <img src="{{ $group->profile->picture_url }}" alt="{{ $group->name }} cover"
                            class="nmtafe-hero rounded-3 mb-3">
                    @endif
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <div class="fw-semibold h5 mb-1">{{ $group->name }}</div>
                            <div class="small text-secondary">Created by {{ $group->creator?->name ?? 'Alumni' }}</div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1">
                            <span class="badge text-bg-danger">{{ $group->members_count }} members</span>
                            <span
                                class="badge text-bg-light">{{ $group->creator_user_id === auth()->id() ? 'Owner' : 'Member' }}</span>
                        </div>
                    </div>
                    <p class="text-secondary">{{ $group->description }}</p>
                    @if ($group->profile?->bio)
                        <p class="text-secondary small mb-2">{{ $group->profile->bio }}</p>
                    @endif
                    @if (!empty($group->profile?->details))
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($group->profile->details as $detail)
                                <span
                                    class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <div class="small text-secondary">{{ $group->projects_count }} projects,
                            {{ $group->research_papers_count }} papers linked</div>
                        <div class="d-flex gap-2">
                            @if ($group->creator_user_id === auth()->id())
                                <form method="POST" action="{{ route('groups.destroy', $group) }}" class="m-0">
                                    <button type="button" class="btn btn-light btn-sm border"
                                        data-toggle-target="group-edit-{{ $group->id }}" data-toggle-open-text="Edit"
                                        data-toggle-close-text="Close" aria-expanded="false">
                                        Edit
                                    </button>
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @if ($group->creator_user_id === auth()->id())
                <div class="nmtafe-masonry-span collapse" id="group-edit-{{ $group->id }}">
                    <div class="nmtafe-card bg-white rounded-4 p-4 border">
                        <form method="POST" action="{{ route('groups.update', $group) }}" class="row g-2">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @method('PATCH')
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $group->name }}"
                                    required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Description</label>
                                <textarea name="description" class="form-control" rows="3" required>{{ $group->description }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Profile Summary</label>
                                <textarea name="profile_bio" class="form-control" rows="3" placeholder="Share a short profile summary">{{ $group->profile?->bio }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Profile picture URL</label>
                                <input type="url" name="picture_url" class="form-control"
                                    value="{{ $group->profile?->picture_url }}"
                                    placeholder="https://example.com/photo.jpg">
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="form-label fw-semibold small mb-0">Details</label>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-add-detail="group-details-edit-{{ $group->id }}">Add detail</button>
                                </div>
                                <div class="d-grid gap-2 mt-2" id="group-details-edit-{{ $group->id }}"
                                    data-next-index="{{ count($group->profile?->details ?? []) }}">
                                    @forelse ($group->profile?->details ?? [] as $index => $detail)
                                        <div class="row g-2 detail-row">
                                            <div class="col">
                                                <input type="text" name="profile_details[{{ $index }}]"
                                                    class="form-control" value="{{ $detail }}"
                                                    placeholder="Detail">
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
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-semibold small">Projects</label>
                                <select name="project_ids[]" class="form-select" multiple>
                                    @php($groupProjectIds = $group->projects->pluck('id')->all())
                                    @forelse ($projects as $project)
                                        <option value="{{ $project->id }}" @selected(in_array($project->id, $groupProjectIds))>
                                            {{ $project->title }}</option>
                                    @empty
                                        <option disabled>No projects available</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-semibold small">Papers</label>
                                <select name="paper_ids[]" class="form-select" multiple>
                                    @php($groupPaperIds = $group->researchPapers->pluck('id')->all())
                                    @forelse ($papers as $paper)
                                        <option value="{{ $paper->id }}" @selected(in_array($paper->id, $groupPaperIds))>
                                            {{ $paper->title }}</option>
                                    @empty
                                        <option disabled>No papers available</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 col-lg-4">
                                <label class="form-label fw-semibold small">Members</label>
                                <select name="member_ids[]" class="form-select" multiple>
                                    @php($groupMemberIds = $group->members->pluck('id')->all())
                                    @forelse ($members as $member)
                                        <option value="{{ $member->id }}" @selected(in_array($member->id, $groupMemberIds))>
                                            {{ $member->name }}</option>
                                    @empty
                                        <option disabled>No members available</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-12 text-end">
                                <button class="btn btn-danger btn-sm" type="submit">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12 w-100" style="column-span: all;">
                <div class="alert alert-light border">
                    {{ !empty($searchQuery) ? 'No managed groups match your search.' : 'You have not joined any groups yet.' }}
                </div>
            </div>
        @endforelse
    </div>

    <script>
        (() => {
            const publicList = document.getElementById('group-public-list');
            const manageList = document.getElementById('group-manage-list');
            const tabButtons = document.querySelectorAll('#groupHeaderTabs [data-bs-toggle="tab"]');

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

                if (event.target.classList.contains('remove-detail')) {
                    event.preventDefault();
                    event.target.closest('.detail-row')?.remove();
                }
            });

            if (!publicList || !manageList || !tabButtons.length) {
                return;
            }

            const setListVisibility = (targetId) => {
                const showManage = targetId === '#group-manage-pane';
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
            setListVisibility(`#group-${initialTab}-pane`);
        })();
    </script>
@endsection
