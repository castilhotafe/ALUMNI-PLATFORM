<?php

namespace App\Http\Controllers\Mobile;

use App\Events\ContributionTriggered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\UpdateProfileRequest;
use App\Http\Resources\MobileProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function show(Request $request): MobileProfileResource
    {
        return new MobileProfileResource($request->user()->load('profile'));
    }

    public function update(UpdateProfileRequest $request): MobileProfileResource
    {
        $data = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            if (array_key_exists('name', $data)) {
                $user->update(['name' => $data['name']]);
            }
            if (array_key_exists('bio', $data)) {
                $profile = $user->profile()->updateOrCreate([], ['bio' => $data['bio'] ?? '']);
                ContributionTriggered::dispatch($user, 'profile_updated', $profile);
            }
        });

        return new MobileProfileResource($user->refresh()->load('profile'));
    }
}
