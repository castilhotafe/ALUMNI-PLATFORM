<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
