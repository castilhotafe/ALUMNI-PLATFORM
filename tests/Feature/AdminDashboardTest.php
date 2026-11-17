<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
