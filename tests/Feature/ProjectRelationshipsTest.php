<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a project stores the fields required by issue 24', function () {
    $project = Project::create([
        'title' => 'Alumni Portfolio',
        'description' => 'A project created by alumni authors.',
        'repo_url' => 'https://github.com/example/alumni-portfolio',
        'demo_url' => 'https://alumni-portfolio.example.com',
        'visibility' => 'public',
    ]);

    $this->assertDatabaseHas('projects', [
        'id' => $project->getKey(),
        'title' => 'Alumni Portfolio',
        'description' => 'A project created by alumni authors.',
        'repo_url' => 'https://github.com/example/alumni-portfolio',
        'demo_url' => 'https://alumni-portfolio.example.com',
        'visibility' => 'public',
    ]);
});

test('project authors can be attached queried and detached', function () {
    $project = Project::create([
        'title' => 'Collaborative Project',
        'description' => 'A project with multiple authors.',
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
        ->toBeTrue()
        ->and($project->users()->whereKey($primaryAuthor->getKey())->exists())
        ->toBeTrue();

    $project->users()->detach($secondaryAuthor);

    $this->assertDatabaseMissing('project_user', [
        'project_id' => $project->getKey(),
        'user_id' => $secondaryAuthor->getKey(),
    ]);
    expect($project->users()->whereKey($secondaryAuthor->getKey())->exists())->toBeFalse();
});
