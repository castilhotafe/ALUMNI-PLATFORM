<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GroupController extends Controller
{
    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $groupData = Arr::only($validated, ['name', 'description']);
        $group = Group::create([
            ...$groupData,
            'creator_user_id' => $request->user()->id,
        ]);

        $memberIds = collect($validated['member_ids'] ?? [])
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $memberMap = $memberIds
            ->mapWithKeys(fn($id) => [$id => ['member_role' => 'member']])
            ->all();
        $memberMap[$request->user()->id] = ['member_role' => 'owner'];

        $group->members()->sync($memberMap);

        $projectIds = collect($validated['project_ids'] ?? [])
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $paperIds = collect($validated['paper_ids'] ?? [])
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $group->projects()->sync($projectIds);
        $group->researchPapers()->sync($paperIds);

        $profileData = [
            'bio' => $validated['profile_bio'] ?? null,
            'details' => collect($validated['profile_details'] ?? [])
                ->filter(fn($detail) => filled($detail))
                ->values()
                ->all(),
            'picture_url' => $validated['picture_url'] ?? null,
        ];

        if (filled($profileData['bio']) || filled($profileData['picture_url']) || !empty($profileData['details'])) {
            $group->profile()->create($profileData);
        }

        return back()->with('status_group', 'Group created.');
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        abort_unless($group->creator_user_id === $request->user()->id, 403);

        $validated = $request->validated();

        $groupData = Arr::only($validated, ['name', 'description']);
        $group->update($groupData);

        $memberIds = collect($validated['member_ids'] ?? [])
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $memberMap = $memberIds
            ->mapWithKeys(fn($id) => [$id => ['member_role' => 'member']])
            ->all();
        $memberMap[$request->user()->id] = ['member_role' => 'owner'];

        $group->members()->sync($memberMap);

        $projectIds = collect($validated['project_ids'] ?? [])
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $paperIds = collect($validated['paper_ids'] ?? [])
            ->filter(fn($id) => filled($id))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $group->projects()->sync($projectIds);
        $group->researchPapers()->sync($paperIds);

        $profileData = [
            'bio' => $validated['profile_bio'] ?? null,
            'details' => collect($validated['profile_details'] ?? [])
                ->filter(fn($detail) => filled($detail))
                ->values()
                ->all(),
            'picture_url' => $validated['picture_url'] ?? null,
        ];

        if ($group->profile) {
            $group->profile->update($profileData);
        } elseif (filled($profileData['bio']) || filled($profileData['picture_url']) || !empty($profileData['details'])) {
            $group->profile()->create($profileData);
        }

        return back()->with('status', 'Group updated.');
    }

    public function destroy(Request $request, Group $group): RedirectResponse
    {
        abort_unless($group->creator_user_id === $request->user()->id, 403);

        $group->delete();

        return back()->with('status', 'Group deleted.');
    }
}
