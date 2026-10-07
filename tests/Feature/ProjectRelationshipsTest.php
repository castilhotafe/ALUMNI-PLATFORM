<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project authors can be attached queried and detached', function () {
    $project = Project::create([
        'title' => 'Collaborative Project',
        'description' => 'A project with ordered authors.',
        'visibility' => 'private',
    ]);
    $primaryAuthor = User::factory()->create();
    $secondaryAuthor = User::factory()->create();

    $project->users()->attach($secondaryAuthor, ['author_order' => 2]);
    $project->users()->attach($primaryAuthor, ['author_order' => 1]);

    expect($project->users()->pluck('users.id')->all())
        ->toBe([$primaryAuthor->getKey(), $secondaryAuthor->getKey()])
        ->and($project->primaryAuthor()->sole()->is($primaryAuthor))
        ->toBeTrue()
        ->and($primaryAuthor->projects()->whereKey($project->getKey())->exists())
        ->toBeTrue();

    $project->users()->detach($secondaryAuthor);

    $this->assertDatabaseMissing('user_projects', [
        'project_id' => $project->getKey(),
        'user_id' => $secondaryAuthor->getKey(),
    ]);
});
