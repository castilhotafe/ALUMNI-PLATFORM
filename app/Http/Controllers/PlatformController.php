<?php

namespace App\Http\Controllers;

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
}
