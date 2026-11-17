<?php

namespace App\Http\Controllers;

use App\Events\ContributionTriggered;
use App\Http\Requests\MeInfoUpdateRequest;
use Illuminate\Http\RedirectResponse;

class MeController extends Controller
{
    public function update(MeInfoUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Get or create profile
        $profile = $user->profile ?? $user->profile()->create();

        // Update profile with validated data
        $profile->update($request->validated());
        ContributionTriggered::dispatch($user, 'profile_updated', $profile);

        return back()->with('status', 'Profile information updated successfully.');
    }
}
