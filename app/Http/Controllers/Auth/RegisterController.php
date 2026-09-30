<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Show the registration view.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        // Data is pre-validated by RegisterRequest
        $validated = $request->validated();

        $roleMap = [
            'current' => ['role' => 'Current Student', 'flag' => 'isCurrent'],
            'alumni' => ['role' => 'Alumni',          'flag' => 'isAlumni'],
            'lecturer' => ['role' => 'Lecturer',        'flag' => 'isLecturer'],
            'partner' => ['role' => 'Partner',         'flag' => 'isPartner'],
            'general' => ['role' => 'General User',    'flag' => null],
        ];

        $selection = $validated['role_selection'];
        $roleConfig = $roleMap[$selection];

        // Prepare boolean flag mappings
        $roleFlags = [
            'isCurrent' => false,
            'isAlumni' => false,
            'isLecturer' => false,
            'isPartner' => false,
        ];

        if ($roleConfig['flag'] !== null) {
            $roleFlags[$roleConfig['flag']] = true;
        }

        $requiresStudentId = in_array($selection, ['current', 'alumni'], true);
        $studentId = $requiresStudentId ? (int) $validated['student_id'] : null;

        // Create user record
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'password_confirmed_at' => now(),
            'student_id' => $studentId,
        ] + $roleFlags);

        // Assign Spatie Role and initialize user profile
        Role::findOrCreate($roleConfig['role']);
        $user->assignRole($roleConfig['role']);
        Profile::firstOrCreate(['user_id' => $user->id]);

        // Trigger email verification event
        event(new Registered($user));

        // Authenticate immediately
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
