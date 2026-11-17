<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::findOrCreate('Admin');
    Role::findOrCreate('General User');
    Role::findOrCreate('Current Student');
    Role::findOrCreate('Alumni');
    Role::findOrCreate('Partner');
    Role::findOrCreate('Lecturer');

    // Disable only the framework CSRF middleware so SubstituteBindings remains active
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('admin can create a new user via admin store', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'new.user@example.test',
            'role_selection' => 'partner',
            'status' => 'active',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseHas('users', ['email' => 'new.user@example.test', 'name' => 'New User']);
    $user = User::where('email', 'new.user@example.test')->first();
    expect($user->hasRole('Partner'))->toBeTrue();
});

test('admin can verify declared role flags via updateVerification', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create(['isCurrent' => true, 'isVerifiedCurrent' => false]);

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.verify', $user), [
            'isVerifiedCurrent' => '1',
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $this->assertTrue($user->refresh()->isVerifiedCurrent);
});

test('admin can change a user declared role and it updates role flags and role', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create(['isCurrent' => true]);
    $user->assignRole('Current Student');

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role_selection' => 'partner',
            'status' => 'active',
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect();

    $user->refresh();

    expect($user->isCurrent)->toBeFalse();
    expect($user->isPartner)->toBeTrue();
    expect($user->hasRole('Partner'))->toBeTrue();
});
