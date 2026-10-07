<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('project migrations provide the required schema', function () {
    expect(Schema::hasColumns('projects', [
        'id',
        'title',
        'description',
        'repo_url',
        'demo_url',
        'visibility',
        'created_at',
        'updated_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('user_projects', [
            'id',
            'user_id',
            'project_id',
            'author_order',
            'created_at',
            'updated_at',
        ]))->toBeTrue();

    $project = Project::create([
        'title' => 'Alumni Portfolio',
        'description' => 'A public project created by alumni.',
        'repo_url' => 'https://github.com/example/alumni-portfolio',
        'demo_url' => 'https://alumni-portfolio.example.com',
        'visibility' => 'public',
    ]);

    $this->assertDatabaseHas('projects', [
        'id' => $project->getKey(),
        'repo_url' => 'https://github.com/example/alumni-portfolio',
        'demo_url' => 'https://alumni-portfolio.example.com',
        'visibility' => 'public',
    ]);
});
