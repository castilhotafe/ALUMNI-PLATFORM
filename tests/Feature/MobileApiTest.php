<?php

use App\Models\Post;
use App\Models\User;

function mobileToken(User $user): string
{
    return $user->createToken('test-device', ['*'], now()->addMinutes(30))->plainTextToken;
}

test('mobile login returns the Flutter profile and a hashed expiring token', function () {
    $user = User::factory()->create(['email' => 'member@example.com']);
    $user->profile()->create(['bio' => 'Graduate']);

    $response = $this->postJson('/api/mobile/login', [
        'login' => ' MEMBER@example.com ', 'password' => 'password',
    ])->assertOk()->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.bio', 'Graduate')
        ->assertJsonStructure(['token', 'data' => ['id', 'name', 'email', 'bio', 'picture_url']]);

    $token = $response->json('token');
    expect($user->tokens()->first()->token)->not->toBe($token);
    expect($user->tokens()->first()->expires_at)->not->toBeNull();
    $this->withToken($token)->getJson('/api/mobile/me')->assertOk()
        ->assertJsonMissingPath('data.password');
});

test('mobile login rejects invalid credentials and unverified accounts', function () {
    $user = User::factory()->unverified()->create();
    $this->postJson('/api/mobile/login', ['login' => $user->email, 'password' => 'wrong'])->assertUnauthorized();
    $this->postJson('/api/mobile/login', ['login' => $user->email, 'password' => 'password'])->assertForbidden();
    $this->postJson('/api/mobile/login', ['login' => 'not-an-email'])->assertUnprocessable()
        ->assertJsonValidationErrors(['login', 'password']);
    expect($user->tokens()->count())->toBe(0);
});

test('mobile login is rate limited', function () {
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/mobile/login', ['login' => 'missing@example.com', 'password' => 'wrong'])->assertUnauthorized();
    }
    $this->postJson('/api/mobile/login', ['login' => 'missing@example.com', 'password' => 'wrong'])->assertStatus(429);
});

test('mobile protected routes reject guests with JSON', function () {
    $this->get('/api/mobile/me')->assertUnauthorized()->assertJsonStructure(['message']);
    $this->patchJson('/api/mobile/me', ['name' => 'Other'])->assertUnauthorized();
    $this->getJson('/api/mobile/feed')->assertUnauthorized();
    $this->getJson('/api/mobile/feed/1')->assertUnauthorized();
    $this->postJson('/api/mobile/logout')->assertUnauthorized();
});

test('mobile profile updates only the authenticated account and creates a missing profile', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $original = $other->name;
    $this->withToken(mobileToken($user))->patchJson('/api/mobile/me', ['name' => '  Updated  ', 'bio' => 'New biography'])
        ->assertOk()->assertJsonPath('data.name', 'Updated')->assertJsonPath('data.bio', 'New biography');
    expect($user->fresh()->name)->toBe('Updated');
    expect($user->fresh()->profile->bio)->toBe('New biography');
    expect($other->fresh()->name)->toBe($original);
    $this->patchJson('/api/mobile/me', ['bio' => ''])->assertOk()->assertJsonPath('data.bio', '');
    expect($user->fresh()->name)->toBe('Updated');
});

test('mobile profile rejects invalid or protected fields', function (array $changes) {
    $user = User::factory()->create();
    $original = $user->name;
    $this->withToken(mobileToken($user))->patchJson('/api/mobile/me', $changes)->assertUnprocessable();
    expect($user->fresh()->name)->toBe($original);
})->with([
    'empty' => [[]],
    'blank name' => [['name' => '   ']],
    'long name' => [['name' => str_repeat('x', 256)]],
    'long bio' => [['bio' => str_repeat('x', 1001)]],
    'wrong type' => [['bio' => ['invalid']]],
    'account id' => [['name' => 'Changed', 'id' => 999]],
    'email' => [['email' => 'other@example.com']],
    'role' => [['name' => 'Changed', 'isLecturer' => true]],
]);

test('mobile profile accepts the maximum field lengths', function () {
    $user = User::factory()->create();
    $this->withToken(mobileToken($user))->patchJson('/api/mobile/me', [
        'name' => str_repeat('n', 255), 'bio' => str_repeat('b', 1000),
    ])->assertOk()->assertJsonPath('data.name', str_repeat('n', 255));
});

test('mobile feed is ordered and paginated and never exposes private posts', function () {
    $user = User::factory()->create();
    $public = Post::factory()->count(21)->create(['user_id' => $user->id, 'visibility' => 'public', 'created_at' => now()]);
    $private = Post::factory()->create(['user_id' => $user->id, 'visibility' => 'private']);
    $this->withToken(mobileToken($user));
    $first = $this->getJson('/api/mobile/feed')->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('next_page', 2);
    $second = $this->getJson('/api/mobile/feed?page=2')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('next_page', null);
    $ids = array_merge(array_column($first->json('data'), 'id'), array_column($second->json('data'), 'id'));
    expect($ids)->toBe($public->pluck('id')->sortDesc()->values()->all());
    $this->getJson('/api/mobile/feed/'.$public->first()->id)->assertOk()
        ->assertJsonPath('data.author_name', $user->name)
        ->assertJsonStructure(['data' => ['id', 'title', 'body', 'author_name', 'created_at']]);
    $this->getJson('/api/mobile/feed/'.$private->id)->assertNotFound();
    $this->getJson('/api/mobile/feed/999999')->assertNotFound();
    $this->getJson('/api/mobile/feed?page=0')->assertUnprocessable();
    $this->getJson('/api/mobile/feed?page=abc')->assertUnprocessable();
    $this->getJson('/api/mobile/feed?page=3')->assertOk()->assertJsonCount(0, 'data')->assertJsonPath('next_page', null);
});

test('mobile logout revokes only its token and revoked tokens are rejected', function () {
    $user = User::factory()->create();
    $token = mobileToken($user);
    $otherToken = mobileToken($user);
    $this->withToken($token)->postJson('/api/mobile/logout')->assertNoContent();
    expect($user->tokens()->count())->toBe(1);
    app('auth')->forgetGuards();
    $this->withToken($token)->getJson('/api/mobile/me')->assertUnauthorized();
    app('auth')->forgetGuards();
    $this->withToken($otherToken)->getJson('/api/mobile/me')->assertOk();
});

test('expired tokens and unverified users cannot read mobile data', function () {
    $user = User::factory()->create();
    $expired = $user->createToken('expired', ['*'], now()->subMinute())->plainTextToken;
    $this->withToken($expired)->getJson('/api/mobile/me')->assertUnauthorized();
    $user->update(['email_verified_at' => null]);
    app('auth')->forgetGuards();
    $this->withToken(mobileToken($user))->getJson('/api/mobile/feed')->assertForbidden();
});

test('a website session cannot replace a mobile bearer token', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'web')->getJson('/api/mobile/me')->assertUnauthorized();
});
