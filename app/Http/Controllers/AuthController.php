<?php
namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\APIResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());
        return APIResponse::successResponse(new UserResource($user), 'User registered successfully', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $tokenData = $this->authService->login($request->validated());

        if (!$tokenData) {
            return APIResponse::errorResponse('Invalid credentials', 401, [], 'UNAUTHORIZED');
        }

        return APIResponse::successResponse($tokenData, 'Login successful');
    }

    public function me(): JsonResponse
    {
        return APIResponse::successResponse(new UserResource($this->authService->me()), 'Authenticated user');
    }

    public function refresh(): JsonResponse
    {
        return APIResponse::successResponse($this->authService->refresh(), 'Token refreshed successfully');
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return APIResponse::successResponse(null, 'Successfully logged out');
    }
}