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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $requiresStudentId = in_array($request->input('role_selection'), ['current', 'alumni'], true);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_selection' => ['required', 'string', Rule::in(['current', 'alumni', 'lecturer', 'partner', 'general'])],
            'student_id' => [
                'nullable',
                Rule::requiredIf($requiresStudentId),
                'digits:8',
            ],
        ]);

        $roleMap = [
            'current' => ['role' => 'Current Student', 'flag' => 'isCurrent'],
            'alumni' => ['role' => 'Alumni', 'flag' => 'isAlumni'],
            'lecturer' => ['role' => 'Lecturer', 'flag' => 'isLecturer'],
            'partner' => ['role' => 'Partner', 'flag' => 'isPartner'],
            'general' => ['role' => 'General User', 'flag' => null],
        ];

        $selection = $request->string('role_selection')->toString();
        $roleConfig = $roleMap[$selection];

        $roleFlags = [
            'isCurrent' => false,
            'isAlumni' => false,
            'isLecturer' => false,
            'isPartner' => false,
        ];
        if ($roleConfig['flag'] !== null) {
            $roleFlags[$roleConfig['flag']] = true;
        }

        $studentId = $requiresStudentId ? (int) $request->input('student_id') : null;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_confirmed_at' => now(),
            'student_id' => $studentId,
        ] + $roleFlags);

        Role::findOrCreate($roleConfig['role']);
        $user->assignRole($roleConfig['role']);
        Profile::firstOrCreate(['user_id' => $user->id]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
