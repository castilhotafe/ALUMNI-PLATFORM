<?php

use App\Models\Group;
use App\Models\GroupProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('GroupProfile Model Relationships & Casts', function () {

    it('belongs to a Group model', function () {
        $group = Group::factory()->create();
        $profile = GroupProfile::create([
            'group_id' => $group->id,
            'bio' => 'Community engineering group',
            'details' => ['Laravel', 'Perth'],
        ]);

        expect($profile->group)->toBeInstanceOf(Group::class)
            ->and($profile->group->id)->toBe($group->id);
    });

    it('casts details attribute to an array', function () {
        $group = Group::factory()->create();
        $profile = GroupProfile::create([
            'group_id' => $group->id,
            'details' => ['Cybersecurity', 'Networking'],
        ]);

        expect($profile->fresh()->details)->toBeArray()
            ->toBe(['Cybersecurity', 'Networking']);
    });
});
