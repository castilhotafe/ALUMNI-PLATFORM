<?php

namespace App\Http\Controllers;

use App\Models\ResearchPaper;
use Illuminate\Http\Request;
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
}
