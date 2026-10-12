<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project contributors can be attached queried and detached', function () {
    $project = Project::create([
        'title' => 'Collaborative Project',
        'description' => 'A project with contributors.',
        'visibility' => 'private',
    ]);
    $developer = User::factory()->create();
    $designer = User::factory()->create();

    $project->users()->attach($developer, ['contribution_role' => 'Developer']);
    $project->users()->attach($designer, ['contribution_role' => 'Designer']);

    expect($project->users()->whereKey($developer->getKey())->exists())
        ->toBeTrue()
        ->and($project->users()->whereKey($developer->getKey())->firstOrFail()->pivot->contribution_role)
        ->toBe('Developer');

    $project->users()->detach($designer);

    $this->assertDatabaseMissing('user_projects', [
        'project_id' => $project->getKey(),
        'user_id' => $designer->getKey(),
    ]);
});
