<?php

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('project collaborators can be created and queried', function () {
    $project = Project::create([
        'title' => 'Collaborative Project',
        'description' => 'A project with an external collaborator.',
        'visibility' => 'public',
    ]);

    $collaborator = $project->collaborators()->create([
        'name' => 'External Contributor',
        'role' => 'Designer',
    ]);

    expect($collaborator->project->is($project))->toBeTrue();

    $this->assertDatabaseHas('project_collaborators', [
        'project_id' => $project->getKey(),
        'name' => 'External Contributor',
        'role' => 'Designer',
    ]);
});
