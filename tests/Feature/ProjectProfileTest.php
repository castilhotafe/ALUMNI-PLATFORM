<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a project can have one profile', function () {
    $project = Project::create([
        'title' => 'Profiled Project',
        'description' => 'A project with additional profile details.',
        'visibility' => 'public',
    ]);

    $profile = $project->profile()->create([
        'bio' => 'Long-form project information.',
        'details' => ['Laravel', 'Alumni'],
        'picture_url' => 'https://example.com/project.png',
    ]);

    expect($profile->details)
        ->toBe(['Laravel', 'Alumni'])
        ->and($profile->project->is($project))
        ->toBeTrue();

    $this->assertDatabaseHas('project_profiles', [
        'project_id' => $project->getKey(),
        'bio' => 'Long-form project information.',
        'picture_url' => 'https://example.com/project.png',
    ]);
});
