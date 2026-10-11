<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaperRequest;
use App\Http\Requests\UpdatePaperRequest;
use App\Models\ResearchPaper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class PaperController extends Controller
{
    public function store(StorePaperRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $paperData = Arr::only($validated, ['title', 'abstract', 'doi', 'pdf_url', 'visibility']);
        $paper = ResearchPaper::create($paperData);
        $paper->users()->attach($request->user()->id, ['author_order' => 1]);

        $authors = collect($validated['authors'] ?? [])
            ->filter(fn ($author) => filled($author['name'] ?? null))
            ->values()
            ->map(fn ($author, int $index) => [
                'name' => $author['name'],
                'author_order' => isset($author['order']) ? (int) $author['order'] : $index + 1,
            ]);

        $ownerName = $request->user()->name;
        if (! $authors->contains(fn ($author) => strcasecmp($author['name'], $ownerName) === 0)) {
            $authors->prepend([
                'name' => $ownerName,
                'author_order' => 1,
            ]);
        }

        if ($authors->isNotEmpty()) {
            $paper->authors()->createMany($authors->values()->all());
        }

        $profileData = [
            'bio' => $validated['profile_bio'] ?? null,
            'details' => collect($validated['profile_details'] ?? [])
                ->filter(fn ($detail) => filled($detail))
                ->values()
                ->all(),
            'picture_url' => $validated['picture_url'] ?? null,
        ];

        if (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $paper->profile()->create($profileData);
        }

        return back()->with('status_paper', 'Research paper created.');
    }

    public function update(UpdatePaperRequest $request, ResearchPaper $paper): RedirectResponse
    {
        $validated = $request->validated();

        $paperData = Arr::only($validated, ['title', 'abstract', 'doi', 'pdf_url', 'visibility']);
        $paper->update($paperData);

        $authors = collect($validated['authors'] ?? [])
            ->filter(fn ($author) => filled($author['name'] ?? null))
            ->values()
            ->map(fn ($author, int $index) => [
                'name' => $author['name'],
                'author_order' => isset($author['order']) ? (int) $author['order'] : $index + 1,
            ]);

        $ownerName = $request->user()->name;
        if (! $authors->contains(fn ($author) => strcasecmp($author['name'], $ownerName) === 0)) {
            $authors->prepend([
                'name' => $ownerName,
                'author_order' => 1,
            ]);
        }

        $paper->authors()->delete();
        if ($authors->isNotEmpty()) {
            $paper->authors()->createMany($authors->values()->all());
        }

        $profileData = [
            'bio' => $validated['profile_bio'] ?? null,
            'details' => collect($validated['profile_details'] ?? [])
                ->filter(fn ($detail) => filled($detail))
                ->values()
                ->all(),
            'picture_url' => $validated['picture_url'] ?? null,
        ];

        if ($paper->profile) {
            $paper->profile->update($profileData);
        } elseif (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $paper->profile()->create($profileData);
        }

        return back()->with('status', 'Research paper updated.');
    }

    public function destroy(ResearchPaper $paper): RedirectResponse
    {
        $paper->delete();

        return back()->with('status', 'Research paper deleted.');
    }
}
