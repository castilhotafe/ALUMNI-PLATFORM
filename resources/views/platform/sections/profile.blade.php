{{-- Named as Me Page --}}
@extends('layouts.platform')

@section('content')
    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border">
        <div class="nmtafe-kicker text-danger fw-semibold mb-3">Your space</div>
        <h2 class="h3 fw-bold mb-2 text-dark">Manage your profile and activity.</h2>
        <p class="text-secondary mb-0">Keep your alumni identity current, track your contributions, and manage your presence
            across the network.</p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-4">

                    @if (!empty($user->profile?->picture_url))
                        <img src="{{ $user->profile->picture_url }}" alt="{{ $user->name }}'s profile picture"
                            class="rounded-circle" style="width: 64px; height: 64px; object-fit: cover;">
                    @else
                        <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                            style="width: 64px; height: 64px; font-size: 24px;">{{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="min-w-0">
                        <div class="h5 mb-0">{{ $user->name }}</div>
                        <div class="small text-secondary text-truncate">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="nmtafe-divider my-4"></div>

                <div id="profileDisplay">
                    <div class="small text-uppercase text-secondary fw-semibold mb-3">Profile summary</div>
                    <p class="text-secondary small mb-3">
                        {{ $user->profile?->bio ?? 'Add a professional summary and highlight your details.' }}</p>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @forelse ($user->profile?->details ?? [] as $detail)
                            <span class="badge rounded-pill text-bg-light text-danger small">{{ $detail }}</span>
                        @empty
                            <span class="badge rounded-pill text-bg-light text-secondary small">No details added yet</span>
                        @endforelse
                    </div>

                    @if ($user->profile?->resume_link)
                        <div class="mb-4">
                            <a href="{{ $user->profile->resume_link }}" target="_blank"
                                class="btn btn-sm btn-outline-danger w-100">View Resume</a>
                        </div>
                    @endif

                    <button type="button" class="btn btn-danger w-100" onclick="toggleProfileEdit()">Edit Profile
                        Info</button>
                </div>

                <div id="profileEditForm" style="display: none;">
                    <form method="POST" action="{{ route('me.profile.update') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="bio" class="form-label small text-uppercase fw-semibold">Professional
                                Summary</label>
                            <textarea name="bio" id="bio" class="form-control form-control-sm" rows="4" maxlength="1000">{{ $user->profile?->bio ?? '' }}</textarea>
                            <small class="text-secondary d-block mt-1">Max 1000 characters</small>
                            @error('bio')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="resume_link" class="form-label small text-uppercase fw-semibold">Resume/CV
                                Link</label>
                            <input type="url" name="resume_link" id="resume_link" class="form-control form-control-sm"
                                placeholder="https://example.com/resume.pdf"
                                value="{{ $user->profile?->resume_link ?? '' }}">
                            @error('resume_link')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="picture_url" class="form-label small text-uppercase fw-semibold">Profile Picture
                                URL</label>
                            <input type="url" name="picture_url" id="picture_url" class="form-control form-control-sm"
                                placeholder="https://example.com/photo.jpg"
                                value="{{ $user->profile?->picture_url ?? '' }}">
                            @error('picture_url')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-uppercase fw-semibold">Details</label>
                            <div id="detailsList">
                                @forelse ($user->profile?->details ?? [] as $detail)
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="text" name="details[]" class="form-control form-control-sm"
                                            style="height: 31px; line-height: 1; vertical-align: middle;"
                                            value="{{ $detail }}" maxlength="100" required>

                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center"
                                            style="height: 31px; padding-top: 0; padding-bottom: 0;"
                                            onclick="removeDetail(this)">
                                            Remove
                                        </button>
                                    </div>
                                @empty
                                @endforelse
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="addDetail()">+ Add
                                Detail</button>
                            @error('details')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                            @error('details.*')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-danger w-100">Save Changes</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                                onclick="toggleProfileEdit()">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="nmtafe-divider my-4"></div>

                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary w-100 btn-sm">Account Settings</a>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100">
                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <div class="nmtafe-kicker text-danger fw-semibold">Your contributions</div>
                        <h3 class="h5 mb-0">Posts, projects, and publications</h3>
                        <p class="text-secondary mt-1">Quickly capture your latest work and ideas here.<br>Visit specific
                            project or post pages to manage in-depth details and team members.</p>
                    </div>
                    <span class="badge text-bg-light text-secondary small">Owner view</span>
                </div>

                <div class="row g-4">
                    <!-- Left Column: Posts -->
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-3 h-100">

                            <!-- Posts Header & Create Toggle -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-semibold small text-uppercase">Your posts</div>
                                    <span class="badge text-bg-light border">{{ $user->posts->count() }}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-toggle-target="collapseNewPost" data-toggle-open-text="+ Post"
                                    data-toggle-close-text="Close" aria-expanded="false">
                                    + Post
                                </button>
                            </div>

                            <!-- Posts Create Form (Collapsible) -->
                            <div class="collapse mb-3 @if ($errors->getBag('post')->any() || session('status_post')) show @endif" id="collapseNewPost">
                                <div class="card card-body bg-light border-0 p-3">
                                    @if (session('status_post'))
                                        <div class="alert alert-success py-2 small mb-3">{{ session('status_post') }}
                                        </div>
                                    @endif
                                    @if ($errors->getBag('post')->any())
                                        <div class="alert alert-danger py-2 small mb-3">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->getBag('post')->all() as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('posts.store') }}" class="row g-2">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-12">
                                            <input type="text" name="title" class="form-control form-control-sm"
                                                placeholder="Post title" value="{{ old('title') }}" required>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="body" class="form-control form-control-sm" rows="2" placeholder="Share an update..."
                                                required>{{ old('body') }}</textarea>
                                        </div>
                                        <div class="col-12 d-flex justify-content-between">
                                            <select name="visibility" class="form-select form-select-sm w-auto"
                                                style="height: 31px;">
                                                <option value="public" @selected(old('visibility') === 'public')>Public</option>
                                                <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                            </select>
                                            <button
                                                class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center"
                                                type="submit" style="height: 31px;">Create Post</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Posts List -->
                            <div>

                                @forelse ($user->posts as $post)
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
                                            <div>
                                                <div class="fw-semibold small">{{ $post->title }}</div>
                                                <div class="text-secondary small">
                                                    {{ \Illuminate\Support\Str::limit($post->body, 80) }}</div>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="{{ route('posts.destroy', $post) }}"
                                                    class="m-0 d-flex">
                                                    <button type="button" class="btn btn-sm btn-light border mx-2"
                                                        data-toggle-target="collapseEditPost{{ $post->id }}"
                                                        data-toggle-open-text="Edit" data-toggle-close-text="Close"
                                                        aria-expanded="false">
                                                        Edit
                                                    </button>

                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>

                                            </div>

                                        </div>

                                        <!-- Inline Edit Form (Collapsible) -->
                                        <div class="collapse mt-2" id="collapseEditPost{{ $post->id }}">
                                            <form method="POST" action="{{ route('posts.update', $post) }}"
                                                class="row g-2 bg-light p-2 rounded border-0">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                @method('PATCH')
                                                <div class="col-12">
                                                    <input type="text" name="title"
                                                        class="form-control form-control-sm" value="{{ $post->title }}"
                                                        required>
                                                </div>
                                                <div class="col-12">
                                                    <textarea name="body" class="form-control form-control-sm" rows="2" required>{{ $post->body }}</textarea>
                                                </div>
                                                <div class="col-12 d-flex justify-content-between">
                                                    <select name="visibility" class="form-select form-select-sm w-auto"
                                                        style="height:31px;">
                                                        <option value="public" @selected($post->visibility === 'public')>Public</option>
                                                        <option value="private" @selected($post->visibility === 'private')>Private
                                                        </option>
                                                    </select>
                                                    <button
                                                        class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center"
                                                        type="submit" style="height:31px;">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-secondary small text-center p-3 bg-light rounded">No posts yet. Use +
                                        Post to add your first post.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Projects & Papers -->
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-3 h-100">

                            <!-- Projects Header & Create Toggle -->
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-semibold small text-uppercase">Projects</div>
                                    <span class="badge text-bg-light border">{{ $user->projects->count() }}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-toggle-target="collapseNewProject" data-toggle-open-text="+ Project"
                                    data-toggle-close-text="Close" aria-expanded="false">
                                    + Project
                                </button>
                            </div>

                            <!-- Projects Create Form (Collapsible) -->
                            <div class="collapse mb-3 @if ($errors->getBag('project')->any() || session('status_project')) show @endif"
                                id="collapseNewProject">
                                <div class="card card-body bg-light border-0 p-3">
                                    @if (session('status_project'))
                                        <div class="alert alert-success py-2 small mb-3">{{ session('status_project') }}
                                        </div>
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
                                    <form method="POST" action="{{ route('projects.store') }}" class="row g-2">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-12">
                                            <input type="text" name="title" class="form-control form-control-sm"
                                                placeholder="Project title" value="{{ old('title') }}" required>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Short description"
                                                required>{{ old('description') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <input type="url" name="repo_url" class="form-control form-control-sm"
                                                placeholder="Repository URL (optional)" value="{{ old('repo_url') }}">
                                        </div>
                                        <div class="col-12 d-flex justify-content-between">
                                            <select name="visibility" class="form-select form-select-sm w-auto"
                                                style="height:31px;">
                                                <option value="public" @selected(old('visibility') === 'public')>Public</option>
                                                <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                            </select>
                                            <button
                                                class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center"
                                                type="submit" style="height:31px;">Create Project</button>
                                        </div>

                                    </form>
                                </div>
                            </div>

                            <!-- Projects List -->
                            <div class="mb-4">
                                @forelse ($user->projects as $project)
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
                                            <div>
                                                <div class="fw-semibold small">{{ $project->title }}</div>
                                                <div class="text-secondary small">
                                                    {{ $project->pivot->contribution_role ?? 'Contributor' }}</div>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                                    class="m-0 d-flex">
                                                    <button type="button" class="btn btn-sm btn-light border mx-2"
                                                        data-toggle-target="collapseEditProject{{ $project->id }}"
                                                        data-toggle-open-text="Edit" data-toggle-close-text="Close"
                                                        aria-expanded="false">
                                                        Edit
                                                    </button>

                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>

                                            </div>

                                        </div>

                                        <!-- Inline Edit Form (Collapsible) -->
                                        <div class="collapse mt-2" id="collapseEditProject{{ $project->id }}">
                                            <form method="POST" action="{{ route('projects.update', $project) }}"
                                                class="row g-2 bg-light p-2 rounded border-0">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                @method('PATCH')
                                                <div class="col-12">
                                                    <input type="text" name="title"
                                                        class="form-control form-control-sm"
                                                        value="{{ $project->title }}" required>
                                                </div>
                                                <div class="col-12">
                                                    <textarea name="description" class="form-control form-control-sm" rows="2" required>{{ $project->description }}</textarea>
                                                </div>

                                                <div class="col-12">
                                                    <input type="url" name="repo_url"
                                                        class="form-control form-control-sm"
                                                        value="{{ $project->repo_url }}" placeholder="Repository URL">
                                                </div>
                                                <div class="col-12 d-flex justify-content-between">
                                                    <select name="visibility" class="form-select form-select-sm w-auto"
                                                        style="height:31px;">
                                                        <option value="public" @selected($project->visibility === 'public')>Public</option>
                                                        <option value="private" @selected($project->visibility === 'private')>Private
                                                        </option>
                                                    </select>
                                                    <button
                                                        class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center"
                                                        type="submit" style="height:31px;">Save Changes</button>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-secondary small text-center p-3 bg-light rounded mb-3">No projects
                                        linked yet.</div>
                                @endforelse
                            </div>

                            <!-- Papers Header & Create Toggle -->
                            <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-semibold small text-uppercase">Research papers</div>
                                    <span class="badge text-bg-light border">{{ $user->researchPapers->count() }}</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-toggle-target="collapseNewPaper" data-toggle-open-text="+ Paper"
                                    data-toggle-close-text="Close" aria-expanded="false">
                                    + Paper
                                </button>
                            </div>

                            <!-- Papers Create Form (Collapsible) -->
                            <div class="collapse mb-3 @if ($errors->getBag('paper')->any() || session('status_paper')) show @endif"
                                id="collapseNewPaper">
                                <div class="card card-body bg-light border-0 p-3">
                                    @if (session('status_paper'))
                                        <div class="alert alert-success py-2 small mb-3">{{ session('status_paper') }}
                                        </div>
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
                                    <form method="POST" action="{{ route('papers.store') }}" class="row g-2">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <div class="col-12">
                                            <input type="text" name="title" class="form-control form-control-sm"
                                                placeholder="Paper title" value="{{ old('title') }}" required>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="abstract" class="form-control form-control-sm" rows="2" placeholder="Abstract" required>{{ old('abstract') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="doi" class="form-control form-control-sm" rows="2" placeholder="DOI (optional)">{{ old('doi') }}</textarea>
                                        </div>
                                        <div class="col-12">
                                            <textarea name="pdf_url" class="form-control form-control-sm" rows="2" placeholder="PDF URL (optional)">{{ old('pdf_url') }}</textarea>
                                        </div>
                                        <div class="col-12 d-flex justify-content-between">
                                            <select name="visibility" class="form-select form-select-sm w-auto"
                                                style="height:31px;">
                                                <option value="public" @selected(old('visibility') === 'public')>Public</option>
                                                <option value="private" @selected(old('visibility') === 'private')>Private</option>
                                            </select>
                                            <button
                                                class="btn btn-sm btn-danger d-inline-flex align-items-center justify-content-center"
                                                type="submit" style="height:31px;">Publish Paper</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Papers List -->
                            <div>
                                @forelse ($user->researchPapers as $paper)
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between gap-2 align-items-start mb-2">
                                            <div>
                                                <div class="fw-semibold small">{{ $paper->title }}</div>
                                                <div class="text-secondary small">Author order:
                                                    {{ $paper->pivot->author_order }}</div>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <form method="POST" action="{{ route('papers.destroy', $paper) }}"
                                                    class="m-0 d-flex">
                                                    <button type="button" class="btn btn-sm btn-light border mx-2"
                                                        data-toggle-target="collapseEditPaper{{ $paper->id }}"
                                                        data-toggle-open-text="Edit" data-toggle-close-text="Close"
                                                        aria-expanded="false">
                                                        Edit
                                                    </button>

                                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>

                                            </div>
                                        </div>

                                        <!-- Inline Edit Form (Collapsible) -->
                                        <div class="collapse mt-2" id="collapseEditPaper{{ $paper->id }}">
                                            <form method="POST" action="{{ route('papers.update', $paper) }}"
                                                class="row g-2 bg-light p-2 rounded border-0">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                @method('PATCH')
                                                <div class="col-12">
                                                    <input type="text" name="title"
                                                        class="form-control form-control-sm" value="{{ $paper->title }}"
                                                        required>
                                                </div>
                                                <div class="col-12">
                                                    <textarea name="abstract" class="form-control form-control-sm" rows="2" required>{{ $paper->abstract }}</textarea>
                                                </div>

                                                <div class="col-12">
                                                    <input type="text" name="doi"
                                                        class="form-control form-control-sm" value="{{ $paper->doi }}"
                                                        placeholder="DOI (optional)">
                                                </div>
                                                <div class="col-12">
                                                    <input type="url" name="pdf_url"
                                                        class="form-control form-control-sm"
                                                        value="{{ $paper->pdf_url }}" placeholder="PDF URL (optional)">
                                                </div>
                                                <div class="col-12 text-end">
                                                    <select name="visibility" class="form-select form-select-sm w-auto"
                                                        style="height:31px;">
                                                        <option value="public" @selected($paper->visibility === 'public')>Public
                                                        </option>
                                                        <option value="private" @selected($paper->visibility === 'private')>Private
                                                        </option>
                                                    </select>
                                                    <button class="btn btn-sm btn-danger" type="submit">Save
                                                        Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-secondary small text-center p-3 bg-light rounded">No papers linked
                                        yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleProfileEdit() {
            const display = document.getElementById('profileDisplay');
            const form = document.getElementById('profileEditForm');

            if (form.style.display === 'none') {
                display.style.display = 'none';
                form.style.display = 'block';
            } else {
                display.style.display = 'block';
                form.style.display = 'none';
            }
        }

        function addDetail() {
            const detailsList = document.getElementById('detailsList');
            const detailDiv = document.createElement('div');
            detailDiv.className = 'd-flex gap-2 mb-2';
            detailDiv.innerHTML = `
                <input type="text" name="details[]"
                                            class="form-control form-control-sm"
                                            style="height: 31px; line-height: 1; vertical-align: middle;"
                                            placeholder="Enter a detail"
                                             maxlength="100" required>

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center"
                                                style="height: 31px; padding-top: 0; padding-bottom: 0;"
                                                onclick="removeDetail(this)">
                                            Remove
                                        </button>
            `;
            detailsList.appendChild(detailDiv);
        }

        function removeDetail(button) {
            button.parentElement.remove();
        }

        function initCollapseToggles() {
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
        }

        // auto-dismiss alerts (success or validation) after a short delay
        document.addEventListener('DOMContentLoaded', function() {
            initCollapseToggles();
            document.addEventListener('click', (event) => {
                const addCollaboratorBtn = event.target.closest('[data-add-collaborator]');
                if (addCollaboratorBtn) {
                    const targetId = addCollaboratorBtn.getAttribute('data-add-collaborator');
                    const container = document.getElementById(targetId);
                    if (container) {
                        addCollaboratorRow(container);
                    }
                    return;
                }

                const addAuthorBtn = event.target.closest('[data-add-author]');
                if (addAuthorBtn) {
                    const targetId = addAuthorBtn.getAttribute('data-add-author');
                    const container = document.getElementById(targetId);
                    if (container) {
                        addAuthorRow(container);
                    }
                    return;
                }

                if (event.target.classList.contains('remove-collaborator')) {
                    event.preventDefault();
                    event.target.closest('.collaborator-row')?.remove();
                    return;
                }

                if (event.target.classList.contains('remove-author')) {
                    event.preventDefault();
                    event.target.closest('.author-row')?.remove();
                }
            });
            setTimeout(() => {
                document.querySelectorAll('.auto-dismiss').forEach(el => {
                    el.style.transition = 'opacity 300ms ease';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 350);
                });
            }, 4000);
        });
    </script>
@endsection
