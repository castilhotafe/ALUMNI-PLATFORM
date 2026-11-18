@extends('layouts.platform')

@section('content')
    @php($headerTab = 'overview')
    @if ($errors->getBag('post')->any() || session('status_post'))
        @php($headerTab = 'post')
    @endif

    <!-- Enhanced Header with Tabs -->
    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border" data-x-data="sectionTabs('{{ $headerTab }}')">
        <div class="d-md-flex align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Welcome back</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Your alumni network at a glance</h2>
                <p class="lead text-secondary mb-0">Track activity, collaborate, and discover opportunities.</p>
            </div>

            <!-- Tab Navigation Buttons -->
            <ul class="nav nav-pills mt-4 mt-md-0 p-1 rounded-3 flex-nowrap" id="headerTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border" id="overview-tab" type="button" role="tab"
                        data-x-on:click="activate('overview')"
                        data-x-bind:class="activeTab === 'overview' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="overview-pane" data-x-bind:aria-selected="activeTab === 'overview'">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border mx-2 text-nowrap" id="post-tab" type="button" role="tab"
                        data-x-on:click="activate('post')"
                        data-x-bind:class="activeTab === 'post' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="post-pane" data-x-bind:aria-selected="activeTab === 'post'">
                        <i class="bi bi-plus-lg me-1"></i> Create Post
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="headerTabsContent">
            <!-- Overview Tab (Empty by default as content sits below, or used for specific header info) -->
            <div class="tab-pane fade {{ $headerTab === 'overview' ? 'show active' : '' }}" id="overview-pane"
                role="tabpanel" aria-labelledby="overview-tab" tabindex="0" data-x-show="activeTab === 'overview'"
                data-x-bind:class="activeTab === 'overview' ? 'show active' : ''">
                <p class="small text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Use the tabs above to quickly share
                    updates with your network.</p>
            </div>

            <!-- Create Post Tab -->
            <div class="tab-pane fade {{ $headerTab === 'post' ? 'show active' : '' }}" id="post-pane" role="tabpanel"
                aria-labelledby="post-tab" tabindex="0" data-x-show="activeTab === 'post'"
                data-x-bind:class="activeTab === 'post' ? 'show active' : ''">
                <div class="pt-4 border-top">
                    @if (session('status_post'))
                        <div class="alert alert-success py-2 small mb-3">{{ session('status_post') }}</div>
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
                    <form method="POST" action="{{ route('posts.store') }}" class="row g-3">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Post title</label>
                            <input type="text" name="title" class="form-control" maxlength="255"
                                placeholder="Give your post a clear title" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Visibility</label>
                            <select name="visibility" class="form-select">
                                <option value="public">Public - Everyone can see</option>
                                <option value="private">Private - Only you</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">What's on your mind?</label>
                            <textarea name="body" class="form-control" rows="3"
                                placeholder="Share your thoughts, updates, or opportunities..." required></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-danger px-4" type="submit">Publish post</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics and Activity Row -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-4">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100 border">
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Platform metrics</div>
                <div class="row g-3">
                    @foreach ($metrics as $metric)
                        <div class="col-6">
                            <div class="metric-box rounded-3 p-3 h-100 bg-light border">
                                <div class="small text-uppercase text-secondary fw-semibold mb-2">{{ $metric['label'] }}
                                </div>
                                <div class="h3 fw-bold mb-1 text-danger">{{ $metric['value'] }}</div>
                                <div class="small text-secondary">{{ $metric['note'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100 border">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <div class="nmtafe-kicker text-danger fw-semibold">Network pulse</div>
                        <h3 class="h4 mb-0 text-dark">Latest activity and updates</h3>
                    </div>
                    <span class="badge text-bg-danger">Live</span>
                </div>

                <div class="d-grid gap-3 nmtafe-list-anchor" id="network-pulse-list">
                    @forelse ($networkPulse as $activity)
                        <div class="activity-item border rounded-3 p-3 transition-all">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                <div class="fw-semibold">{{ ucfirst($activity['type']) }} update</div>
                                <span
                                    class="badge text-bg-light text-danger small">{{ strtoupper($activity['type']) }}</span>
                            </div>
                            <div class="text-secondary small mb-2">
                                {{ $activity['item']->title ?? ($activity['item']->name ?? ($activity['item']->company ?? 'Item')) }}
                            </div>
                            <p class="mb-2 text-secondary">
                                {{ \Illuminate\Support\Str::limit($activity['item']->description ?? ($activity['item']->abstract ?? ''), 120) }}
                            </p>
                            <div class="small text-uppercase text-secondary fw-semibold">
                                {{ $activity['time']->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-secondary text-center py-3">No recent platform activity.</div>
                    @endforelse
                </div>

                @php($activityQuery = request()->query())
                <x-compact-pagination route-name="dashboard" page-name="activity_page" :current-page="$networkPulsePagination['current_page']" :total-pages="$networkPulsePagination['total_pages']"
                    :query-params="$activityQuery" anchor="network-pulse-list" />
            </div>
        </div>
    </div>

    <!-- Content and Profiles Row -->
    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100 border">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <div class="nmtafe-kicker text-danger fw-semibold">Recent content</div>
                        <h3 class="h5 mb-0 text-dark">What alumni are sharing</h3>
                    </div>
                </div>

                <div class="d-grid gap-3 nmtafe-list-anchor" id="recent-content-list">
                    @forelse ($recentContent as $entry)
                        @if ($entry['type'] === 'post')
                            @php($post = $entry['item'])
                            <div class="content-item border rounded-3 p-3">
                                <div class="d-flex align-items-start justify-content-between mb-2 gap-2">
                                    <div class="fw-semibold small">{{ $post->title }}</div>
                                    <span
                                        class="badge text-bg-light text-secondary small flex-shrink-0">{{ $post->user?->name ?? 'Alumni' }}</span>
                                </div>
                                <p class="text-secondary small mb-0">
                                    {{ \Illuminate\Support\Str::limit($post->body, 120) }}</p>
                            </div>
                        @else
                            @php($message = $entry['item'])
                            <div class="content-item border rounded-3 p-3">
                                <div class="d-flex align-items-start justify-content-between mb-2 gap-2">
                                    <div class="fw-semibold small">Message from {{ $message->sender?->name ?? 'Unknown' }}
                                    </div>
                                    <span class="badge text-bg-light text-secondary small flex-shrink-0">Message</span>
                                </div>
                                <p class="text-secondary small mb-0">
                                    {{ \Illuminate\Support\Str::limit($message->content, 120) }}</p>
                            </div>
                        @endif
                    @empty
                        <div class="text-secondary text-center py-4">No recent content yet.</div>
                    @endforelse
                </div>

                @php($contentQuery = request()->query())
                <x-compact-pagination route-name="dashboard" page-name="content_page" :current-page="$recentContentPagination['current_page']" :total-pages="$recentContentPagination['total_pages']"
                    :query-params="$contentQuery" anchor="recent-content-list" />
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="nmtafe-card bg-white rounded-4 p-4 h-100 border">
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Active alumni</div>
                <h3 class="h5 mb-4 text-dark">Featured community members</h3>
                <div class="d-grid gap-3 nmtafe-list-anchor" id="members-list">
                    @foreach ($topProfiles as $profileUser)
                        <div class="profile-item d-flex align-items-center justify-content-between border rounded-3 p-3">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                    style="width: 40px; height: 40px; font-size: 16px;">
                                    {{ strtoupper(substr($profileUser->name, 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <div class="fw-semibold small text-dark">{{ $profileUser->name }}</div>
                                    <div class="small text-secondary text-truncate">
                                        {{ $profileUser->profile?->details ? implode(', ', array_slice($profileUser->profile->details, 0, 2)) : 'Profile pending' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-end small text-secondary flex-shrink-0">
                                <div>{{ $profileUser->posts_count }} posts</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @php($membersQuery = request()->query())
                <x-compact-pagination route-name="dashboard" page-name="members_page" :current-page="$membersPagination['current_page']" :total-pages="$membersPagination['total_pages']"
                    :query-params="$membersQuery" anchor="members-list" />
            </div>
        </div>
    </div>

@endsection
