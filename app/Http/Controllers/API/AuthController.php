<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Auth\RegisterRequest;
use App\Http\Requests\API\Auth\LoginRequest;
use App\Http\Resources\API\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'access_key' => $user->access_key,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->validated());
    }

    public function refreshAccessKey(Request $request)
    {
        $user = $request->user();

        $user = $this->authService->refreshAccessKey($user);

        return response()->json([
            'message' => 'Access key refreshed successfully',
            'user' => new \App\Http\Resources\API\UserResource($user),
            'access_key' => $user->access_key,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $this->authService->logout($user);

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json(new UserResource($request->user()));
    }
}