<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertGuest;

describe('Web Authentication Logic', function () {
    beforeEach(function () {
        // Disable only the framework CSRF middleware so SubstituteBindings remains active
        $this->withoutMiddleware(PreventRequestForgery::class);
    });

    describe('Registration (POST)', function () {

        it('registers a user with valid data, assigns roles, and triggers verification', function () {
            // Fake events so Laravel doesn't try to send a real email during the test

            Event::fake();

            $data = [
                'name' => 'Alumni User',
                'email' => 'alumni@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_selection' => 'alumni',
                'student_id' => '12345678', // Now explicitly required by your Form Request on Alumni
            ];

            $this->from('/register')
                ->post('/register', $data)
                ->assertRedirect(route('dashboard'));

            assertAuthenticated();

            // Verify the new boolean flags were set correctly
            assertDatabaseHas('users', [
                'email' => 'alumni@example.com',
                'isAlumni' => true,
                'student_id' => 12345678,
            ]);

            // Assert the email verification event fired
            Event::assertDispatched(Registered::class);
        });

        it('requires a student ID when registering as an alumni or current student', function () {
            $data = [
                'name' => 'Alumni User',
                'email' => 'alumni@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role_selection' => 'alumni',
                // Missing student_id
            ];

            $this->from('/register')
                ->post('/register', $data)
                ->assertSessionHasErrors('student_id'); // Checks your conditional validation

            assertGuest();
        });

        it('prevents registration with empty data and flashes session errors', function () {
            $this->from('/register')
                ->post('/register', [])
                ->assertSessionHasErrors(['name', 'email', 'password', 'role_selection']);

            assertGuest();
        });
    });

    describe('Login (POST)', function () {

        it('authenticates a user with correct credentials and redirects', function () {
            $user = User::factory()->create([
                'email_verified_at' => now(), // Ensures they bypass any 'verified' middleware
            ]);

            $this->from('/login')
                ->post('/login', [
                    'email' => $user->email,
                    'password' => 'password',
                ])
                ->assertRedirect(route('dashboard'));

            assertAuthenticatedAs($user);
        });

        it('rejects login attempts with bad credentials', function () {
            $user = User::factory()->create();

            $this->from('/login')
                ->post('/login', [
                    'email' => $user->email,
                    'password' => 'wrongpassword',
                ])
                ->assertSessionHasErrors('email')
                ->assertRedirect('/login');

            assertGuest();
        });
    });

    describe('Logout (POST)', function () {

        it('allows an authenticated user to logout and redirects to home', function () {
            $user = User::factory()->create();

            actingAs($user)
                ->post('/logout')
                ->assertRedirect('/');

            assertGuest();
        });

        it('prevents unauthenticated users from accessing the logout route', function () {
            $this->post('/logout')
                ->assertRedirect(route('login'));
        });
    });
});
// test 2
