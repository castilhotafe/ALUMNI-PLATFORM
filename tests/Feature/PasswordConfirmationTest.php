<?php

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Password Confirmation Logic', function () {
    beforeEach(function () {
        $this->withoutMiddleware(PreventRequestForgery::class);
    });

    test('confirm password screen can be rendered', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertOk()
            ->assertViewIs('auth.confirm-password');
    });

    test('password can be confirmed with valid password', function () {
        $user = User::factory()->create([
            'password_confirmed_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('auth.password_confirmed_at');

        expect($user->fresh()->password_confirmed_at)->not->toBeNull();
    });

    test('password is not confirmed with invalid password', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/confirm-password')
            ->post('/confirm-password', [
                'password' => 'wrong-password',
            ]);

        $response->assertRedirect('/confirm-password')
            ->assertSessionHasErrors('password');
    });
});
