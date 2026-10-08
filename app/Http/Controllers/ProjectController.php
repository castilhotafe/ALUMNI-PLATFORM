<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

class ProjectController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $projectData = Arr::only($validated, ['title', 'description', 'repo_url', 'visibility']);
        $project = Project::create($projectData);
        $project->users()->attach($request->user()->id, ['contribution_role' => 'Owner']);

        $collaborators = collect($validated['collaborators'] ?? [])
            ->filter(fn ($collaborator) => filled($collaborator['name'] ?? null))
            ->map(fn ($collaborator) => [
                'name' => $collaborator['name'],
                'role' => $collaborator['role'] ?? null,
            ]);

        $ownerName = $request->user()->name;
        if (! $collaborators->contains(fn ($collaborator) => strcasecmp($collaborator['name'], $ownerName) === 0)) {
            $collaborators->push([
                'name' => $ownerName,
                'role' => 'Owner',
            ]);
        }

        if ($collaborators->isNotEmpty()) {
            $project->collaborators()->createMany($collaborators->values()->all());
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
            $project->profile()->create($profileData);
        }

        return back()->with('status_project', 'Project created.');
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $validated = $request->validated();

        $projectData = Arr::only($validated, ['title', 'description', 'repo_url', 'visibility']);
        $project->update($projectData);

        $collaborators = collect($validated['collaborators'] ?? [])
            ->filter(fn ($collaborator) => filled($collaborator['name'] ?? null))
            ->map(fn ($collaborator) => [
                'name' => $collaborator['name'],
                'role' => $collaborator['role'] ?? null,
            ]);

        $ownerName = $request->user()->name;
        if (! $collaborators->contains(fn ($collaborator) => strcasecmp($collaborator['name'], $ownerName) === 0)) {
            $collaborators->push([
                'name' => $ownerName,
                'role' => 'Owner',
            ]);
        }

        $project->collaborators()->delete();
        if ($collaborators->isNotEmpty()) {
            $project->collaborators()->createMany($collaborators->values()->all());
        }

        $profileData = [
            'bio' => $validated['profile_bio'] ?? null,
            'details' => collect($validated['profile_details'] ?? [])
                ->filter(fn ($detail) => filled($detail))
                ->values()
                ->all(),
            'picture_url' => $validated['picture_url'] ?? null,
        ];

        if ($project->profile) {
            $project->profile->update($profileData);
        } elseif (filled($profileData['bio']) || filled($profileData['picture_url']) || ! empty($profileData['details'])) {
            $project->profile()->create($profileData);
        }

        return back()->with('status', 'Project updated.');
    }

    /**
     * Remove the specified resource from storage.
     */

}
