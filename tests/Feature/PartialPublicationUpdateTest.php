<?php

use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Publication Owner']);
    $this->actingAs($this->user);
    $this->withoutMiddleware(PreventRequestForgery::class);
});

dataset('partial publications', [
    'project' => [Project::class, 'projects.update', 'description', 'collaborators', ['contribution_role' => 'Owner']],
    'paper' => [ResearchPaper::class, 'papers.update', 'abstract', 'authors', ['author_order' => 1]],
]);

it('preserves omitted collaborators or authors and profile data during a compact Me edit', function (
    string $model,
    string $route,
    string $description,
    string $relation,
    array $pivot
) {
    $publication = $model::factory()->create();
    $publication->users()->attach($this->user->id, $pivot);
    $person = $publication->$relation()->create(['name' => 'Existing Contributor']);
    $profile = $publication->profile()->create([
        'bio' => 'Existing biography',
        'picture_url' => 'https://example.com/existing.png',
        'details' => ['Existing detail'],
    ]);

    $originalProfile = $profile->fresh()->getAttributes();

    $this->patch(route($route, $publication), [
        'title' => 'Edited from Me',
        $description => 'Edited description',
        'visibility' => 'private',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $publication->refresh();

    expect($publication->title)->toBe('Edited from Me')
        ->and($publication->$description)->toBe('Edited description')
        ->and($publication->visibility)->toBe('private')
        ->and($publication->$relation()->sole()->id)->toBe($person->id)
        ->and($publication->profile->getAttributes())->toBe($originalProfile);
})->with('partial publications');

it('changes only the supplied profile field and preserves omitted fields', function (
    string $model,
    string $route,
    string $description,
    string $relation,
    array $pivot,
    string $input,
    string $attribute,
    mixed $value,
    mixed $expected
) {
    $publication = $model::factory()->create();
    $publication->users()->attach($this->user->id, $pivot);
    $person = $publication->$relation()->create(['name' => 'Existing Contributor']);
    $original = [
        'bio' => 'Existing biography',
        'picture_url' => 'https://example.com/existing.png',
        'details' => ['Existing detail'],
    ];
    $publication->profile()->create($original);

    $this->patch(route($route, $publication), [
        'title' => 'Updated publication',
        $description => 'Updated description',
        'visibility' => 'public',
        $input => $value,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $publication->refresh();

    expect($publication->profile->only(array_keys($original)))
        ->toBe(array_replace($original, [$attribute => $expected]))
        ->and($publication->$relation()->sole()->id)->toBe($person->id);
})->with('partial publications')->with([
    'biography' => ['profile_bio', 'bio', 'Updated biography', 'Updated biography'],
    'picture' => ['picture_url', 'picture_url', 'https://example.com/new.png', 'https://example.com/new.png'],
    'details' => ['profile_details', 'details', ['New detail', ''], ['New detail']],
    'clear biography' => ['profile_bio', 'bio', '', null],
    'clear picture' => ['picture_url', 'picture_url', '', null],
    'clear details' => ['profile_details', 'details', [], []],
]);

it('handles an explicitly empty contributor list while retaining the owner and profile', function (
    string $model,
    string $route,
    string $description,
    string $relation,
    array $pivot
) {
    $publication = $model::factory()->create();
    $publication->users()->attach($this->user->id, $pivot);
    $publication->$relation()->create(['name' => 'Removed Contributor']);
    $profile = $publication->profile()->create(['bio' => 'Keep this biography']);
    $originalProfile = $profile->fresh()->getAttributes();

    $this->patch(route($route, $publication), [
        'title' => 'Updated publication',
        $description => 'Updated description',
        'visibility' => 'public',
        $relation => [],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($publication->$relation()->sole()->name)->toBe($this->user->name)
        ->and($publication->fresh()->profile->getAttributes())->toBe($originalProfile);
})->with('partial publications');

it('creates a missing profile from the supplied field without adding omitted contributors', function (
    string $model,
    string $route,
    string $description,
    string $relation,
    array $pivot
) {
    $publication = $model::factory()->create();
    $publication->users()->attach($this->user->id, $pivot);

    $this->patch(route($route, $publication), [
        'title' => 'Updated publication',
        $description => 'Updated description',
        'visibility' => 'public',
        'picture_url' => 'https://example.com/new.png',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($publication->fresh()->profile->picture_url)->toBe('https://example.com/new.png')
        ->and($publication->fresh()->profile->bio)->toBeNull()
        ->and($publication->fresh()->profile->details)->toBe([])
        ->and($publication->$relation()->count())->toBe(0);
})->with('partial publications');

it('does not create a profile when profile fields are omitted or explicitly empty', function (
    string $model,
    string $route,
    string $description,
    string $relation,
    array $pivot,
    array $profileFields
) {
    $publication = $model::factory()->create();
    $publication->users()->attach($this->user->id, $pivot);

    $this->patch(route($route, $publication), [
        'title' => 'Updated publication',
        $description => 'Updated description',
        'visibility' => 'public',
        ...$profileFields,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($publication->fresh()->profile)->toBeNull()
        ->and($publication->$relation()->count())->toBe(0);
})->with('partial publications')->with([
    'omitted profile' => [[]],
    'empty profile' => [['profile_bio' => '', 'picture_url' => '', 'profile_details' => []]],
]);
