<?php

use App\Models\Profile;
use App\Models\User;

it('a user can have one profile', function () {
    $user = User::factory()->create();

    $profile = Profile::create([
        'user_id' => $user->id,
        'bio' => 'Test bio',
        'details' => ['location' => 'Perth'],
        'picture_url' => null,
        'resume_link' => null,
    ]);

    expect($user->profile->id)->toBe($profile->id);
    expect($profile->user->id)->toBe($user->id);
});
