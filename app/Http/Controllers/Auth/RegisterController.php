<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $roles = [
            'current' => [
                'spatie' => 'Current Student',
                'flag' => 'isCurrent',
            ],
            'alumni' => [
                'spatie' => 'Alumni',
                'flag' => 'isAlumni',
            ],
            'lecturer' => [
                'spatie' => 'Lecturer',
                'flag' => 'isLecturer',
            ],
            'partner' => [
                'spatie' => 'Partner',
                'flag' => 'isPartner',
            ],
            'general' => [
                'spatie' => 'General User',
                'flag' => null,
            ],
        ];

        $selectedRole = $roles[$data['role']];

        $user = DB::transaction(function () use ($data, $selectedRole) {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),

                'student_id' => in_array(
                    $data['role'],
                    ['current', 'alumni'],
                    true
                ) ? $data['student_id'] : null,

                'isCurrent' => false,
                'isAlumni' => false,
                'isLecturer' => false,
                'isPartner' => false,
            ];

            if ($selectedRole['flag'] !== null) {
                $attributes[$selectedRole['flag']] = true;
            }

            $user = User::create($attributes);

            $user->assignRole($selectedRole['spatie']);

            // Profile creation will be added once the
            // Profile model/migration from Issue #32 is available.

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
