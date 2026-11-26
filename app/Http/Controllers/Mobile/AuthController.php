<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\LoginRequest;
use App\Http\Resources\MobileProfileResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['login'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Check your email and password.'], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Verify your email on the website first.'], 403);
        }

        $token = $user->createToken('alumni-mobile', ['*'], now()->addMinutes(30));

        return response()->json([
            'token' => $token->plainTextToken,
            'data' => new MobileProfileResource($user->load('profile')),
        ]);
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
