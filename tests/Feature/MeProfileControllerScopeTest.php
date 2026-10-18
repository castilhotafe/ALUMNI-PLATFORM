<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Requests\MeInfoUpdateRequest;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ResearchPaper;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

beforeEach(function () {
    // Issue #35 ports controllers and requests only. These isolated routes exercise
    // them with web sessions/auth without claiming production routing is complete.
    Route::middleware(['web', 'auth'])->group(function () {
        Route::patch('/_scope/me-info', [MeController::class, 'update']);
        Route::patch('/_scope/account-profile', [ProfileController::class, 'update']);
        Route::delete('/_scope/account-profile', [ProfileController::class, 'destroy']);
        Route::get('/_scope/account-profile/edit', fn () => 'Profile form')
            ->name('profile.edit');
    });
    Route::getRoutes()->refreshNameLookups();

    $this->withoutMiddleware(PreventRequestForgery::class);
});

it('accepts the optional personal information fields and their validation boundaries', function (array $payload) {
    $request = new MeInfoUpdateRequest;

    expect($request->authorize())->toBeTrue()
        ->and(Validator::make($payload, $request->rules())->passes())->toBeTrue();
})->with([
    'omitted optional fields' => [[]],
    'nullable optional fields' => [[
        'bio' => null,
        'details' => null,
        'resume_link' => null,
        'picture_url' => null,
    ]],
    'maximum accepted lengths and count' => [[
        'bio' => str_repeat('b', 1000),
        'details' => array_fill(0, 20, str_repeat('d', 100)),
        'resume_link' => 'https://example.com/resume.pdf',
        'picture_url' => 'https://example.com/avatar.png',
    ]],
]);

it('rejects invalid personal information through the real form request without updating the profile', function (array $payload, string $field) {
    $user = User::factory()->create();
    $profile = $user->profile()->create(['bio' => 'Original bio.', 'details' => ['PHP']]);
    $original = $profile->fresh()->getAttributes();

    $this->actingAs($user)->from('/_scope/me-info')
        ->patch('/_scope/me-info', $payload)
        ->assertRedirect('/_scope/me-info')
        ->assertSessionHasErrors($field);

    expect($profile->fresh()->getAttributes())->toBe($original);

    // The inherited messages() arrays fall back to Laravel's string messages.
    foreach (session('errors')->get($field) as $message) {
        expect($message)->toBeString();
    }
})->with([
    'bio must be text' => [['bio' => 42], 'bio'],
    'bio length' => [['bio' => str_repeat('b', 1001)], 'bio'],
    'details must be an array' => [['details' => 'PHP'], 'details'],
    'detail count' => [['details' => array_fill(0, 21, 'PHP')], 'details'],
    'detail must be text' => [['details' => [42]], 'details.0'],
    'detail length' => [['details' => [str_repeat('d', 101)]], 'details.0'],
    'resume must be a URL' => [['resume_link' => 'not-a-url'], 'resume_link'],
    'picture must be a URL' => [['picture_url' => 'not-a-url'], 'picture_url'],
]);

it('creates personal information only for the authenticated user from validated fields', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $otherProfile = $other->profile()->create(['bio' => 'Keep this bio.']);

    $this->actingAs($user)->from('/_scope/me-info')
        ->patch('/_scope/me-info', [
            'bio' => 'Software engineer and graduate.',
            'details' => ['PHP', 'Research'],
            'picture_url' => 'https://example.com/avatar.png',
            'user_id' => $other->id,
            'id' => $otherProfile->id,
            'name' => 'Do not change the account name',
            'resume_url' => 'https://example.com/unvalidated.pdf',
        ])
        ->assertRedirect('/_scope/me-info')
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Profile information updated successfully.');

    $profile = $user->fresh()->profile;

    expect(Profile::count())->toBe(2)
        ->and($profile->user_id)->toBe($user->id)
        ->and($profile->bio)->toBe('Software engineer and graduate.')
        ->and($profile->details)->toBe(['PHP', 'Research'])
        ->and($profile->picture_url)->toBe('https://example.com/avatar.png')
        ->and($profile->resume_link)->toBeNull()
        ->and($otherProfile->fresh()->bio)->toBe('Keep this bio.')
        ->and($user->fresh()->name)->toBe($user->name);
});

it('updates the existing personal profile without creating another row', function () {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'bio' => 'Original bio.',
        'details' => ['PHP'],
        'picture_url' => 'https://example.com/original.png',
    ]);

    $this->actingAs($user)->from('/_scope/me-info')
        ->patch('/_scope/me-info', ['bio' => 'Updated bio.', 'details' => ['Laravel']])
        ->assertRedirect('/_scope/me-info')
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Profile information updated successfully.');

    expect($user->profile()->count())->toBe(1)
        ->and($profile->fresh()->bio)->toBe('Updated bio.')
        ->and($profile->fresh()->details)->toBe(['Laravel'])
        ->and($profile->fresh()->picture_url)->toBe('https://example.com/original.png');
});

it('allows personal information to be explicitly cleared', function () {
    $user = User::factory()->create();
    $profile = $user->profile()->create([
        'bio' => 'Original bio.',
        'details' => ['PHP'],
        'picture_url' => 'https://example.com/original.png',
    ]);

    $this->actingAs($user)->patch('/_scope/me-info', [
        'bio' => null,
        'details' => null,
        'picture_url' => null,
    ])->assertSessionHasNoErrors();

    expect($profile->fresh()->bio)->toBeNull()
        ->and($profile->fresh()->details)->toBeNull()
        ->and($profile->fresh()->picture_url)->toBeNull();
});

it('persists or clears a validated resume link on the current profile', function (?string $resumeLink) {
    $user = User::factory()->create();
    $profile = $user->profile()->create(['resume_link' => 'https://example.com/original.pdf']);

    $this->actingAs($user)->patch('/_scope/me-info', [
        'resume_link' => $resumeLink,
    ])->assertSessionHasNoErrors();

    expect($profile->fresh()->resume_link)->toBe($resumeLink);
})->with([
    'replace an existing link' => ['https://example.com/replacement.pdf'],
    'clear an existing link' => [null],
]);

it('persists a resume link when creating the current users first profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->patch('/_scope/me-info', [
        'resume_link' => 'https://example.com/first.pdf',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->profile->resume_link)->toBe('https://example.com/first.pdf');
});

it('passes the current user to the account edit view', function () {
    $user = User::factory()->create();
    $request = Request::create('/_scope/account-profile/edit');
    $request->setUserResolver(fn () => $user);
    $view = Mockery::mock(View::class);

    // The Blade views are out of scope; assert the view contract without rendering.
    $this->mock(Factory::class)->shouldReceive('make')->once()
        ->with('profile.edit', ['user' => $user], [])->andReturn($view);

    expect((new ProfileController)->edit($request))->toBe($view);
});

it('updates only the current account and resets verification when its email changes', function () {
    $user = User::factory()->create(['email' => 'original@example.com']);
    $originalPassword = $user->getRawOriginal('password');
    $other = User::factory()->create();

    $this->actingAs($user)->patch('/_scope/account-profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'id' => $other->id,
        'user_id' => $other->id,
        'password' => 'unvalidated-password',
        'isVerifiedAlumni' => true,
    ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', 'Profile updated.');

    expect($user->fresh()->name)->toBe('Updated Name')
        ->and($user->fresh()->email)->toBe('updated@example.com')
        ->and($user->fresh()->email_verified_at)->toBeNull()
        ->and($user->fresh()->password)->toBe($originalPassword)
        ->and($user->fresh()->isVerifiedAlumni)->toBeFalse()
        ->and($other->fresh()->name)->toBe($other->name)
        ->and($other->fresh()->email)->toBe($other->email);
});

it('keeps email verification when the current email is unchanged', function () {
    $user = User::factory()->create(['email' => 'same@example.com']);
    $verifiedAt = $user->email_verified_at->toDateTimeString();

    $this->actingAs($user)->patch('/_scope/account-profile', [
        'name' => 'Updated Name',
        'email' => $user->email,
    ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Updated Name')
        ->and($user->fresh()->email_verified_at->toDateTimeString())->toBe($verifiedAt);
});

it('rejects invalid account information without changing the current user', function (array $changes, string $field) {
    $user = User::factory()->create(['email' => 'original@example.com']);
    User::factory()->create(['email' => 'occupied@example.com']);
    $original = $user->fresh()->getAttributes();

    $this->actingAs($user)->from('/_scope/account-profile/edit')
        ->patch('/_scope/account-profile', array_replace([
            'name' => $user->name,
            'email' => $user->email,
        ], $changes))
        ->assertRedirect('/_scope/account-profile/edit')
        ->assertSessionHasErrors($field);

    expect($user->fresh()->getAttributes())->toBe($original);
})->with([
    'name is required' => [['name' => ''], 'name'],
    'name must be text' => [['name' => 42], 'name'],
    'name length' => [['name' => str_repeat('n', 256)], 'name'],
    'email is required' => [['email' => ''], 'email'],
    'email must be valid' => [['email' => 'not-an-email'], 'email'],
    'email must be lowercase' => [['email' => 'Uppercase@example.com'], 'email'],
    'email length' => [['email' => str_repeat('e', 250).'@example.com'], 'email'],
    'email must be unique' => [['email' => 'occupied@example.com'], 'email'],
]);

it('deletes the current account after validating its password and clears its session', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['profile_scope_marker' => 'private', '_token' => 'old-token'])
        ->delete('/_scope/account-profile', ['password' => 'password', 'user_id' => $other->id])
        ->assertRedirect('/')
        ->assertSessionHasNoErrors()
        ->assertSessionMissing('profile_scope_marker');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertDatabaseHas('users', ['id' => $other->id]);

    expect(session()->token())->toBeString()->not->toBe('old-token');
});

it('keeps the account and session when deletion password validation fails', function (array $payload) {
    $user = User::factory()->create();

    $this->actingAs($user)->from('/_scope/account-profile/edit')
        ->withSession(['profile_scope_marker' => 'private'])
        ->delete('/_scope/account-profile', $payload)
        ->assertRedirect('/_scope/account-profile/edit')
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertSessionHas('profile_scope_marker', 'private');

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
})->with([
    'incorrect password' => [['password' => 'wrong-password']],
    'missing password' => [[]],
]);

it('loads the current users personal hub with ordered posts and ten newest projects and papers', function () {
    $user = User::factory()->create();
    $profile = $user->profile()->create(['bio' => 'Current user profile.']);
    $other = User::factory()->create();
    $projectIds = [];
    $paperIds = [];
    $postIds = [];

    for ($day = 1; $day <= 12; $day++) {
        $createdAt = sprintf('2026-01-%02d 12:00:00', $day);
        $project = Project::factory()->create(['created_at' => $createdAt, 'visibility' => 'private']);
        $project->users()->attach($user->id, ['contribution_role' => 'Owner']);
        $project->collaborators()->create(['name' => 'Collaborator '.$day, 'role' => 'Developer']);
        $projectIds[] = $project->id;

        $paper = ResearchPaper::factory()->create(['created_at' => $createdAt, 'visibility' => 'private']);
        $paper->users()->attach($user->id, ['author_order' => 1]);
        $paper->authors()->create(['name' => 'Author '.$day, 'author_order' => 1]);
        $paperIds[] = $paper->id;

        $post = $user->posts()->create(['title' => 'Post '.$day, 'body' => 'Post body.', 'visibility' => 'private']);
        $post->forceFill(['created_at' => $createdAt])->save();
        $postIds[] = $post->id;
    }

    $otherProject = Project::factory()->create(['created_at' => '2026-02-01 12:00:00']);
    $otherProject->users()->attach($other->id, ['contribution_role' => 'Owner']);
    $otherPaper = ResearchPaper::factory()->create(['created_at' => '2026-02-01 12:00:00']);
    $otherPaper->users()->attach($other->id, ['author_order' => 1]);
    $other->posts()->create(['title' => 'Other post', 'body' => 'Other user body.']);

    $request = Request::create('/me', 'GET', ['q' => '  unmatched search  ']);
    $request->setUserResolver(fn () => $user);
    $data = [];
    $view = Mockery::mock(View::class);

    $this->mock(Factory::class)->shouldReceive('make')->once()
        ->with('platform.section', Mockery::on(function (array $values) use (&$data) {
            $data = $values;

            return true;
        }), [])->andReturn($view);

    expect((new PlatformController)->me($request))->toBe($view)
        ->and($data['section'])->toBe('me')
        ->and($data['pageTitle'])->toBe('Me')
        ->and($data['sectionKicker'])->toBe('Personal hub')
        ->and($data['searchQuery'])->toBe('unmatched search')
        ->and($data['user'])->toBe($user)
        ->and($user->relationLoaded('profile'))->toBeTrue()
        ->and($user->profile->id)->toBe($profile->id)
        ->and($user->relationLoaded('posts'))->toBeTrue()
        ->and($user->posts->modelKeys())->toBe(array_reverse($postIds))
        ->and($user->relationLoaded('projects'))->toBeTrue()
        ->and($user->projects->modelKeys())->toBe(array_slice(array_reverse($projectIds), 0, 10))
        ->and($user->relationLoaded('researchPapers'))->toBeTrue()
        ->and($user->researchPapers->modelKeys())->toBe(array_slice(array_reverse($paperIds), 0, 10));

    foreach ($user->projects as $project) {
        expect($project->relationLoaded('collaborators'))->toBeTrue()
            ->and($project->collaborators)->toHaveCount(1);
    }

    foreach ($user->researchPapers as $paper) {
        expect($paper->relationLoaded('authors'))->toBeTrue()
            ->and($paper->authors)->toHaveCount(1);
    }
});

it('returns an empty personal hub and blank search for a new account', function () {
    $user = User::factory()->create();
    $request = Request::create('/me');
    $request->setUserResolver(fn () => $user);
    $data = [];
    $view = Mockery::mock(View::class);

    $this->mock(Factory::class)->shouldReceive('make')->once()
        ->with('platform.section', Mockery::on(function (array $values) use (&$data) {
            $data = $values;

            return true;
        }), [])->andReturn($view);

    expect((new PlatformController)->me($request))->toBe($view)
        ->and($data['searchQuery'])->toBe('')
        ->and($data['user']->profile)->toBeNull()
        ->and($data['user']->posts)->toBeEmpty()
        ->and($data['user']->projects)->toBeEmpty()
        ->and($data['user']->researchPapers)->toBeEmpty();
});
