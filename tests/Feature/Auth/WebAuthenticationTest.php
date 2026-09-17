<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('registers a current student and assigns the correct role', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test Student',
        'email' => 'student@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'current',
        'student_id' => '12345678',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'student@example.com')->firstOrFail();

    expect($user->isCurrent)->toBeTrue()
        ->and($user->isAlumni)->toBeFalse()
        ->and($user->isLecturer)->toBeFalse()
        ->and($user->isPartner)->toBeFalse()
        ->and($user->student_id)->toBe(12345678)
        ->and($user->hasRole('Current Student'))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('requires a student id for current students', function () {
    $response = $this->from('/')
        ->post(route('register.store'), [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'current',
        ]);

    $response->assertRedirect('/');
    $response->assertSessionHasErrors('student_id');

    $this->assertDatabaseMissing('users', [
        'email' => 'student@example.com',
    ]);
});

it('requires an eight digit student id', function () {
    $response = $this->from('/')
        ->post(route('register.store'), [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'alumni',
            'student_id' => '1234',
        ]);

    $response->assertRedirect('/');
    $response->assertSessionHasErrors('student_id');
});

it('registers a general user without a student id', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'General User',
        'email' => 'general@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'general',
    ]);

    $response->assertRedirect(route('dashboard'));

    $user = User::where('email', 'general@example.com')->firstOrFail();

    expect($user->student_id)->toBeNull()
        ->and($user->isCurrent)->toBeFalse()
        ->and($user->isAlumni)->toBeFalse()
        ->and($user->isLecturer)->toBeFalse()
        ->and($user->isPartner)->toBeFalse()
        ->and($user->hasRole('General User'))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('logs in a user with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $response = $this->post(route('login.store'), [
        'email' => 'user@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid login credentials', function () {
    User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $response = $this->from('/')
        ->post(route('login.store'), [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

    $response->assertRedirect('/');
    $response->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('logout'));

    $response->assertRedirect(route('login'));

    $this->assertGuest();
});
