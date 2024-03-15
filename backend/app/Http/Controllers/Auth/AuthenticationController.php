<?php

namespace App\Http\Controllers\Auth;

use App\Http\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\{LoginRequest, RegisterRequest};
use App\Models\User;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Hash;

class AuthenticationController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'email' => $request->email,
            'username' => User::createUsernameFromEmail($request->email),
            'password' => Hash::make($request->password),
        ]);

        // Log user in when they register
        auth()->attempt($request->only('email', 'password'), true);

        return ApiResponse::created($user);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!auth()->attempt($credentials, true)) {
            return ApiResponse::unauthorized('Email/password incorrect');
        }

        /** @var \App\Models\User */
        $user = auth()->user();

        return ApiResponse::success($user);
    }

    public function logout(Request $request)
    {
        $request->session()->regenerate();
        $request->user()->tokens()->delete();

        return ApiResponse::success();
    }
}
