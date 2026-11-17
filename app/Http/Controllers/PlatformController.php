<?php

namespace App\Http\Controllers;

use App\Models\EnrolmentListing;
use App\Models\Group;
use App\Models\JobListing;
use App\Models\Post;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlatformController extends Controller
{
    private function searchQuery(Request $request): string
    {
        return trim((string) $request->query('q', ''));
    }

    private function selectedDetails(Request $request): array
    {
        return collect($request->input('details', []))
            ->flatten()
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeSearchValues(array $values): Collection
    {
        return collect($values)
            ->flatMap(function ($value) {
                if ($value instanceof Collection) {
                    return $value->all();
                }

                if (is_array($value)) {
                    return $value;
                }

                return [$value];
            })
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();
    }

    private function matchesSearch(array $values, string $search, array $selectedDetails = []): bool
    {
        $tokens = $this->normalizeSearchValues($values);

        if ($search !== '' && ! Str::contains(Str::lower($tokens->implode(' ')), Str::lower($search))) {
            return false;
        }

        if ($selectedDetails === []) {
            return true;
        }

        $normalizedTokens = $tokens->map(fn ($value) => Str::lower($value))->all();

        foreach ($selectedDetails as $detail) {
            if (! in_array(Str::lower($detail), $normalizedTokens, true)) {
                return false;
            }
        }

        return true;
    }

    private function paginateFilteredCollection(Collection $items, int $perPage, Request $request, string $pageName): LengthAwarePaginator
    {
        $currentPage = max(1, (int) $request->query($pageName, 1));

        return new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
            ]
        )->withQueryString();
    }

    private function communityRoles(): array
    {
        return ['Current Student', 'Alumni', 'Lecturer', 'Partner', 'General User', 'Admin'];
    }

    private function paginateCollection(Collection $items, int $perPage, int $page): array
    {
        $totalItems = $items->count();
        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        $currentPage = max(1, min($page, $totalPages));

        return [
            'items' => $items->forPage($currentPage, $perPage)->values(),
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'per_page' => $perPage,
        ];
    }

    public function projects(Request $request): View
    {
        $user = $request->user();
        $search = $this->searchQuery($request);
        $selectedDetails = $this->selectedDetails($request);
        $allProjects = Project::with(['users.profile', 'collaborators', 'profile'])
            ->visibleTo($user)
            ->withCount('collaborators')
            ->latest()
            ->get();

        $projects = $allProjects
            ->filter(fn ($project) => $this->matchesSearch([
                $project->title,
                $project->description,
                $project->profile?->bio,
                $project->profile?->details ?? [],
                $project->users?->pluck('name') ?? [],
            ], $search, $selectedDetails))
            ->values();

        $userProjects = $user->projects()
            ->with(['collaborators', 'profile'])
            ->withCount('collaborators')
            ->orderByDesc('projects.created_at')
            ->get()
            ->filter(fn ($project) => $this->matchesSearch([
                $project->title,
                $project->description,
                $project->profile?->bio,
                $project->profile?->details ?? [],
                $project->collaborators?->pluck('name') ?? [],
            ], $search, $selectedDetails))
            ->values();

        $detailOptions = $allProjects
            ->flatMap(fn ($project) => $project->profile?->details ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('platform.section', [
            'section' => 'projects',
            'pageTitle' => 'Projects',
            'sectionKicker' => 'Collaboration',
            'projects' => $projects,
            'userProjects' => $userProjects,
            'searchQuery' => $search,
            'selectedDetails' => $selectedDetails,
            'detailOptions' => $detailOptions,
        ]);
    }

    public function papers(Request $request): View
    {
        $user = $request->user();
        $search = $this->searchQuery($request);
        $selectedDetails = $this->selectedDetails($request);
        $allPapers = ResearchPaper::with(['users.profile', 'authors', 'profile'])
            ->visibleTo($user)
            ->withCount('authors')
            ->latest()
            ->get();

        $papers = $allPapers
            ->filter(fn ($paper) => $this->matchesSearch([
                $paper->title,
                $paper->abstract,
                $paper->doi,
                $paper->profile?->bio,
                $paper->profile?->details ?? [],
                $paper->authors?->pluck('name') ?? [],
            ], $search, $selectedDetails))
            ->values();
        $userPapers = $user->researchPapers()
            ->with(['authors', 'profile'])
            ->withCount('authors')
            ->orderByDesc('research_papers.created_at')
            ->get()
            ->filter(fn ($paper) => $this->matchesSearch([
                $paper->title,
                $paper->abstract,
                $paper->doi,
                $paper->profile?->bio,
                $paper->profile?->details ?? [],
                $paper->authors?->pluck('name') ?? [],
            ], $search, $selectedDetails))
            ->values();

        $detailOptions = $allPapers
            ->flatMap(fn ($paper) => $paper->profile?->details ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('platform.section', [
            'section' => 'papers',
            'pageTitle' => 'Research Papers',
            'sectionKicker' => 'Knowledge sharing',
            'papers' => $papers,
            'userPapers' => $userPapers,
            'searchQuery' => $search,
            'selectedDetails' => $selectedDetails,
            'detailOptions' => $detailOptions,
        ]);
    }

    public function me(Request $request): View
    {
        $search = $this->searchQuery($request);
        $user = $request->user()->load([
            'profile',
            'posts' => fn ($query) => $query->latest(),
            'projects' => fn ($query) => $query->with('collaborators')->orderByDesc('projects.created_at')->take(10),
            'researchPapers' => fn ($query) => $query->with('authors')->orderByDesc('research_papers.created_at')->take(10),
        ]);

        return view('platform.section', [
            'section' => 'me',
            'pageTitle' => 'Me',
            'sectionKicker' => 'Personal hub',
            'user' => $user,
            'searchQuery' => $search,
        ]);
    }

    public function directory(Request $request): View
    {
        $search = $this->searchQuery($request);

        $selectedDetails = $this->selectedDetails($request);
        $allUsers = User::with(['profile', 'posts'])->withCount(['projects', 'researchPapers'])
            ->role($this->communityRoles())
            ->orderBy('name')
            ->get();

        $users = $allUsers
            ->filter(fn ($user) => $this->matchesSearch([
                $user->name,
                $user->profile?->bio,
                $user->profile?->details ?? [],
            ], $search, $selectedDetails))
            ->values();

        $detailOptions = $allUsers
            ->flatMap(fn ($user) => $user->profile?->details ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('platform.section', [
            'section' => 'directory',
            'pageTitle' => 'Developer Directory',
            'sectionKicker' => 'Find alumni',
            'users' => $users,
            'searchQuery' => $search,
            'selectedDetails' => $selectedDetails,
            'detailOptions' => $detailOptions,
        ]);
    }

    public function networking(Request $request): View
    {
        $user = $request->user();
        $search = $this->searchQuery($request);
        $selectedDetails = $this->selectedDetails($request);
        $allGroups = Group::with(['creator.profile', 'members.profile', 'projects', 'researchPapers', 'profile'])
            ->withCount(['members', 'projects', 'researchPapers'])
            ->latest()
            ->get();

        $groups = $allGroups
            ->filter(fn ($group) => $this->matchesSearch([
                $group->name,
                $group->description,
                $group->profile?->bio,
                $group->profile?->details ?? [],
                $group->creator?->name,
                $group->members?->pluck('name') ?? [],
                $group->projects?->pluck('title') ?? [],
                $group->researchPapers?->pluck('title') ?? [],
            ], $search, $selectedDetails))
            ->values();

        $memberGroups = Group::with(['creator.profile', 'members.profile', 'projects', 'researchPapers', 'profile'])
            ->whereHas('members', fn ($query) => $query->whereKey($user->id))
            ->withCount(['members', 'projects', 'researchPapers'])
            ->orderByDesc('groups.created_at')
            ->get()
            ->filter(fn ($group) => $this->matchesSearch([
                $group->name,
                $group->description,
                $group->profile?->bio,
                $group->profile?->details ?? [],
                $group->creator?->name,
                $group->members?->pluck('name') ?? [],
                $group->projects?->pluck('title') ?? [],
                $group->researchPapers?->pluck('title') ?? [],
            ], $search, $selectedDetails))
            ->values();

        $detailOptions = $allGroups
            ->flatMap(fn ($group) => $group->profile?->details ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $projects = Project::visibleTo($user)->orderBy('title')->get(['id', 'title']);
        $papers = ResearchPaper::visibleTo($user)->orderBy('title')->get(['id', 'title']);
        $members = User::role($this->communityRoles())->orderBy('name')->get(['id', 'name']);

        return view('platform.section', [
            'section' => 'networking',
            'pageTitle' => 'Networking Hub',
            'sectionKicker' => 'Communities',
            'groups' => $groups,
            'memberGroups' => $memberGroups,
            'projects' => $projects,
            'papers' => $papers,
            'members' => $members,
            'searchQuery' => $search,
            'selectedDetails' => $selectedDetails,
            'detailOptions' => $detailOptions,
        ]);
    }

    private function canPostListings(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        // Require explicit partner verification for users who declared as Partner.
        // Even if permissions exist, a Partner should not be allowed to post until
        // an admin has set `isVerifiedPartner` to true.
        if (($user->isPartner && ! $user->isVerifiedPartner) || ($user->isLecturer && ! $user->isVerifiedLecturer)) {
            return false;
        }

        return $user->hasAnyPermission(['create job listings', 'create enrolment listings']);
    }

    public function discover(Request $request): View
    {
        $search = $this->searchQuery($request);
        $selectedDetails = $this->selectedDetails($request);

        $allJobListings = JobListing::with('creator.profile')->latest()->get();
        $allEnrolmentListings = EnrolmentListing::with('creator.profile')->latest()->get();
        $canPostListings = $this->canPostListings();

        $manageableJobListings = collect();
        $manageableEnrolmentListings = collect();
        if ($canPostListings) {
            $user = $request->user();

            $manageableJobListings = JobListing::with('creator.profile')
                ->when(! $user->hasRole('Admin'), fn ($query) => $query->where('creator_user_id', $user->id))
                ->latest()
                ->get();

            $manageableEnrolmentListings = EnrolmentListing::with('creator.profile')
                ->when(! $user->hasRole('Admin'), fn ($query) => $query->where('creator_user_id', $user->id))
                ->latest()
                ->get();
        }

        $jobListingsFiltered = $allJobListings->filter(fn ($listing) => $this->matchesSearch([
            $listing->title,
            $listing->company,
            $listing->description,
            $listing->profile?->bio,
            $listing->profile?->details ?? [],
        ], $search, $selectedDetails))->values();

        $enrolmentListingsFiltered = $allEnrolmentListings->filter(fn ($listing) => $this->matchesSearch([
            $listing->course_title,
            $listing->tafe_provider,
            $listing->description,
            $listing->profile?->bio,
            $listing->profile?->details ?? [],
        ], $search, $selectedDetails))->values();

        $manageableJobListings = $manageableJobListings->filter(fn ($listing) => $this->matchesSearch([
            $listing->title,
            $listing->company,
            $listing->description,
            $listing->profile?->bio,
            $listing->profile?->details ?? [],
        ], $search, $selectedDetails))->values();

        $manageableEnrolmentListings = $manageableEnrolmentListings->filter(fn ($listing) => $this->matchesSearch([
            $listing->course_title,
            $listing->tafe_provider,
            $listing->description,
            $listing->profile?->bio,
            $listing->profile?->details ?? [],
        ], $search, $selectedDetails))->values();

        $jobListings = $this->paginateFilteredCollection($jobListingsFiltered, 6, $request, 'jobsPage');
        $enrolmentListings = $this->paginateFilteredCollection($enrolmentListingsFiltered, 6, $request, 'enrolmentsPage');

        $detailOptions = collect()
            ->concat($allJobListings)
            ->concat($allEnrolmentListings)
            ->concat($manageableJobListings)
            ->concat($manageableEnrolmentListings)
            ->flatMap(fn ($listing) => $listing->profile?->details ?? [])
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('platform.section', [
            'section' => 'discover',
            'pageTitle' => 'Discover Hub',
            'sectionKicker' => 'Opportunities',
            'jobListings' => $jobListings,
            'enrolmentListings' => $enrolmentListings,
            'manageableJobListings' => $manageableJobListings,
            'manageableEnrolmentListings' => $manageableEnrolmentListings,
            'canPostListings' => $canPostListings,
            'searchQuery' => $search,
            'selectedDetails' => $selectedDetails,
            'detailOptions' => $detailOptions,
        ]);
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $search = $this->searchQuery($request);

        $metrics = [
            ['label' => 'Recent posts', 'value' => Post::visibleTo($user)->count(), 'note' => 'Updates from alumni and staff'],
            ['label' => 'Projects', 'value' => Project::count(), 'note' => 'Collaborative workspaces'],
            ['label' => 'Research Papers', 'value' => ResearchPaper::count(), 'note' => 'Academic contributions'],
            ['label' => 'Groups', 'value' => Group::count(), 'note' => 'Networking circles'],
            ['label' => 'Job Listings', 'value' => JobListing::count(), 'note' => 'Opportunities for alumni'],
            ['label' => 'Enrolment Listings', 'value' => EnrolmentListing::count(), 'note' => 'Educational opportunities'],
            ['label' => 'Community members', 'value' => User::role($this->communityRoles())->count(), 'note' => 'Active directory profiles'],
            ['label' => 'Partner Organisations', 'value' => User::where('isPartner', true)->count(), 'note' => 'Verified partner organisations'],
            ['label' => 'Lecturers', 'value' => User::where('isLecturer', true)->count(), 'note' => 'Verified lecturers'],
            ['label' => 'Alumni', 'value' => User::where('isAlumni', true)->count(), 'note' => 'Verified alumni'],
            ['label' => 'Current Students', 'value' => User::where('isCurrent', true)->count(), 'note' => 'Verified current students'],
            ['label' => 'General Users', 'value' => User::whereHas('roles', fn ($query) => $query->where('name', 'General User'))->count(), 'note' => 'Users without specific roles'],
        ];

        $recentPostsAll = Post::with('user.profile')->visibleTo($user)->latest()->get();
        $recentContentAll = $recentPostsAll->map(fn ($post) => [
            'type' => 'post',
            'item' => $post,
            'time' => $post->created_at,
        ])->sortByDesc('time')->values();

        $projectsAll = Project::with('users.profile')->visibleTo($user)->latest()->get();
        $papersAll = ResearchPaper::with('users.profile')->visibleTo($user)->latest()->get();
        $groupsAll = Group::with('creator.profile')->latest()->get();
        $jobListingsAll = JobListing::with('creator.profile')->latest()->get();
        $enrolmentListingsAll = EnrolmentListing::with('creator.profile')->latest()->get();

        $networkPulseAll = collect()
            ->concat($projectsAll->map(fn ($project) => [
                'type' => 'project',
                'item' => $project,
                'time' => $project->created_at,
            ]))
            ->concat($papersAll->map(fn ($paper) => [
                'type' => 'paper',
                'item' => $paper,
                'time' => $paper->created_at,
            ]))
            ->concat($groupsAll->map(fn ($group) => [
                'type' => 'group',
                'item' => $group,
                'time' => $group->created_at,
            ]))
            ->concat($jobListingsAll->map(fn ($jobListing) => [
                'type' => 'job',
                'item' => $jobListing,
                'time' => $jobListing->created_at,
            ]))
            ->concat($enrolmentListingsAll->map(fn ($enrolmentListing) => [
                'type' => 'enrolment',
                'item' => $enrolmentListing,
                'time' => $enrolmentListing->created_at,
            ]))
            ->sortByDesc('time')
            ->values();

        $topProfilesAll = User::with('profile')->withCount(['posts', 'projects', 'researchPapers'])
            ->role($this->communityRoles())
            ->latest()
            ->get();

        $perPage = 6;
        $contentPage = (int) $request->query('content_page', 1);
        $activityPage = (int) $request->query('activity_page', 1);
        $membersPage = (int) $request->query('members_page', 1);

        $recentContentPagination = $this->paginateCollection($recentContentAll, $perPage, $contentPage);
        $networkPulsePagination = $this->paginateCollection($networkPulseAll, $perPage, $activityPage);
        $membersPagination = $this->paginateCollection($topProfilesAll, $perPage, $membersPage);

        return view('platform.section', [
            'section' => 'dashboard',
            'pageTitle' => 'Dashboard',
            'sectionKicker' => 'Overview',
            'metrics' => $metrics,
            'recentContent' => $recentContentPagination['items'],
            'networkPulse' => $networkPulsePagination['items'],
            'topProfiles' => $membersPagination['items'],
            'recentContentPagination' => $recentContentPagination,
            'networkPulsePagination' => $networkPulsePagination,
            'membersPagination' => $membersPagination,
        ]);
    }
}
