<?php

namespace App\Http\Controllers;

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

        return back()->with('status', 'Profile information updated successfully.');
    }
}
