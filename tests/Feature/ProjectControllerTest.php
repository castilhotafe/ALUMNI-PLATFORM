<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create an authenticated user before each test runs
    $this->user = User::factory()->create(['name' => 'John Doe']);
    $this->actingAs($this->user);
    $this->withoutMiddleware(PreventRequestForgery::class);
});

/*
|--------------------------------------------------------------------------
| Store Method Tests
|--------------------------------------------------------------------------
*/

it('stores a new project with owner mapping, collaborators, and a profile', function () {
    $payload = [
        'title' => 'Awesome Open Source Project',
        'description' => 'A Laravel-based system.',
        'repo_url' => 'https://github.com',
        'visibility' => 'public',
        'collaborators' => [
            ['name' => 'Jane Smith', 'role' => 'Developer'],
            ['name' => '', 'role' => 'Ignored Blank'], // Should be filtered out
        ],
        'profile_bio' => 'Project long description and bio.',
        'profile_details' => ['Laravel 11', 'Pest PHP', ''], // Empty details should be filtered out
        'picture_url' => 'https://example.com',
    ];

    // Assuming your routes look like: Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    $response = $this->post(route('projects.store'), $payload);

    // 1. Assert redirection and flash status
    $response->assertRedirect();
    $response->assertSessionHas('status_project', 'Project created.');

    // 2. Assert basic project details were written to DB
    $this->assertDatabaseHas('projects', [
        'title' => 'Awesome Open Source Project',
        'visibility' => 'public',
    ]);

    $project = Project::first();

    // 3. Assert owner relationship via users pivoting
    expect($project->users->contains($this->user))->toBeTrue()
        ->and($project->users()->first()->pivot->contribution_role)->toBe('Owner');

    // 4. Assert collaborators (Jane Smith + Automatically added Owner)
    expect($project->collaborators)->toHaveCount(2);
    $this->assertDatabaseHas('project_collaborators', [
        'project_id' => $project->id,
        'name' => 'Jane Smith',
        'role' => 'Developer',
    ]);
    $this->assertDatabaseHas('project_collaborators', [
        'project_id' => $project->id,
        'name' => 'John Doe',
        'role' => 'Owner',
    ]);

    // 5. Assert Profile was correctly attached and empty elements removed
    expect($project->profile)->not->toBeNull();
    $this->assertDatabaseHas('project_profiles', [ // Replace with your exact profile table name
        'project_id' => $project->id,
        'bio' => 'Project long description and bio.',
        'picture_url' => 'https://example.com',
    ]);

    // Check that details filtered out the blank string
    expect($project->profile->details)->toEqual(['Laravel 11', 'Pest PHP']);
});

it('does not create a profile if all profile fields are empty', function () {
    $payload = [
        // Ensure these match your validation rules exactly
        'title' => 'Minimal Valid Project Title',
        'description' => 'A valid project description.',
        'repo_url' => 'https://github.com',
        'visibility' => 'private',
        'collaborators' => [],
        // The profile fields we intentionally leave blank/null:
        'profile_bio' => '',
        'profile_details' => [],
        'picture_url' => null,
    ];

    $response = $this->post(route('projects.store'), $payload);

    // Assert it passed validation and redirected instead of throwing a validation error
    $response->assertRedirect();

    $project = Project::first();

    expect($project)->not->toBeNull()
        ->and($project->profile)->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Update Method Tests
|--------------------------------------------------------------------------
*/

it('updates a project and completely refreshes collaborators and profiles', function () {
    // Arrange: Assign ownership to the authenticated user ($this->user)
    $project = Project::factory()->create(['title' => 'Old Title']);
    $project->users()->attach($this->user->id, ['contribution_role' => 'Owner']);

    $project->collaborators()->create(['name' => 'Old Developer', 'role' => 'Helper']);
    $project->profile()->create(['bio' => 'Old Bio', 'details' => ['Vue']]);

    $updatePayload = [
        'title' => 'Brand New Title',
        'description' => 'An updated project description.',
        'repo_url' => 'https://github.com',
        'visibility' => 'public',
        'collaborators' => [
            ['name' => 'Alex Rivera', 'role' => 'Designer'],
        ],
        'profile_bio' => 'Updated Bio String',
        'profile_details' => ['React'],
    ];

    $response = $this->patch(route('projects.update', $project), $updatePayload);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Project updated.');

    expect($project->fresh()->title)->toBe('Brand New Title');
    $this->assertDatabaseMissing('project_collaborators', ['name' => 'Old Developer']);
    expect($project->collaborators()->get())->toHaveCount(2);

    expect($project->profile()->count())->toBe(1)
        ->and($project->fresh()->profile->bio)->toBe('Updated Bio String')
        ->and($project->fresh()->profile->details)->toEqual(['React']);
});

/*
|--------------------------------------------------------------------------
| Destroy Method Tests
|--------------------------------------------------------------------------
*/

it('deletes the specified project', function () {
    $project = Project::factory()->create();

    $response = $this->delete(route('projects.destroy', $project));

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Project deleted.');

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});
