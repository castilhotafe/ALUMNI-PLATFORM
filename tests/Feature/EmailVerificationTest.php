<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

describe('Email Verification Logic', function () {
    beforeEach(function () {
        // Disable only the framework CSRF middleware so SubstituteBindings remains active
        $this->withoutMiddleware(PreventRequestForgery::class);
    });

    test('email verification screen can be rendered', function () {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    });

    test('email can be verified', function () {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    });

    test('email is not verified with invalid hash', function () {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    });

    test('email verification notification can be sent', function () {
        $user = User::factory()->unverified()->create();

        Notification::fake();

        $response = $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo($user, VerifyEmail::class);
        $response->assertSessionHas('status', 'Verification link sent');
    });

    test('email verification screen renders successfully for unverified users', function () {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    });

    test('already verified user is redirected when requesting verification notification', function () {
        $user = User::factory()->create(); // Already verified

        $response = $this->actingAs($user)->post('/email/verification-notification');

        $response->assertRedirect(route('dashboard', absolute: false));
    });

    test('already verified users are redirected to dashboard from verification prompt', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(), // Already verified
        ]);

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertRedirect(route('dashboard', absolute: false));
    });
});
