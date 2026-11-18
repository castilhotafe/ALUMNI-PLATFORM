<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::findOrCreate('Admin');
    Role::findOrCreate('General User');
    Role::findOrCreate('Lecturer');
    $this->withoutMiddleware(PreventRequestForgery::class);
});

test('admin dashboard is accessible to admins', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('Admin Dashboard');
});

test('admin user roles stay locked during updates', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $actor = User::factory()->create();
    $actor->assignRole('Admin');

    $response = $this
        ->actingAs($actor)
        ->patch(route('admin.users.update', $admin), [
            'name' => 'Locked Admin',
            'email' => 'locked-admin@example.com',
            'role_selection' => 'general',
            'status' => 'active',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $admin->refresh();

    expect($admin->name)->toBe('Locked Admin');
    expect($admin->email)->toBe('locked-admin@example.com');
    expect($admin->hasRole('Admin'))->toBeTrue();
});

test('admin can update a non-admin user role during verification', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create(['isLecturer' => false, 'isVerifiedLecturer' => false]);
    $user->assignRole('General User');

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->patch(route('admin.users.verify', $user), [
            'isVerifiedLecturer' => '1',
            'role_selection' => 'lecturer',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status', 'Verification updated for '.$user->name.'.');

    $user->refresh();

    expect($user->isLecturer)->toBeTrue()
        ->and($user->isVerifiedLecturer)->toBeTrue()
        ->and($user->hasRole('Lecturer'))->toBeTrue();
});

test('admin can update a user password and confirm timestamp', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $user = User::factory()->create(['password_confirmed_at' => null]);
    $user->assignRole('General User');

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->patch(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role_selection' => 'general',
            'status' => 'active',
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status', 'User updated.');

    $user->refresh();

    expect($user->password_confirmed_at)->not->toBeNull()
        ->and(Hash::check('NewSecurePass123!', $user->password))->toBeTrue();
});

test('admin can delete a user account', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('Admin');

    $targetUser = User::factory()->create(['name' => 'Delete Me']);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->delete(route('admin.users.destroy', $targetUser));

    $response
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('status', 'User deleted: Delete Me.');

    $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
});
