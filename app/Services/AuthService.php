<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Repositories\AuthRepository;
use App\Traits\APIResponse;

class AuthService
{
    use APIResponse;

    private AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data)
    {
        $data['role'] = $data['role'] ?? RoleEnum::CUSTOMER;
        $user = $this->authRepository->createUser($data);

        return $this->successResponse($user, 'User registered successfully', 201);
    }

    public function login(array $credentials)
    {
        $token = $this->authRepository->findUserByCredentials($credentials);

        if (!$token) {
            return null;
        }

        return $this->authRepository->respondWithToken($token);
    }

    public function me()
    {
        return $this->authRepository->getAuthenticatedUser();
    }

    public function refresh()
    {
        return $this->authRepository->refresh();
    }

    public function logout()
    {
        $this->authRepository->logoutUser();
    }
}