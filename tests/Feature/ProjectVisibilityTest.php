<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project visibility includes public projects and linked private projects', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $publicProject = Project::create([
        'title' => 'Public Project',
        'description' => 'Visible to everyone.',
        'visibility' => 'public',
    ]);
    $privateProject = Project::create([
        'title' => 'Private Project',
        'description' => 'Visible to its contributors.',
        'visibility' => 'private',
    ]);
    $otherPrivateProject = Project::create([
        'title' => 'Other Private Project',
        'description' => 'Visible to another contributor.',
        'visibility' => 'private',
    ]);

    $privateProject->users()->attach($user, ['contribution_role' => 'Developer']);
    $otherPrivateProject->users()->attach($otherUser, ['contribution_role' => 'Developer']);

    expect(Project::visibleTo(null)->pluck('id')->all())
        ->toBe([$publicProject->getKey()])
        ->and(Project::visibleTo($user)->pluck('id')->all())
        ->toBe([$publicProject->getKey(), $privateProject->getKey()]);
});
