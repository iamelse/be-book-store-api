<?php

namespace App\Services;

use App\Http\Resources\API\UserResource;
use App\Repositories\AuthRepository;
use Illuminate\Http\JsonResponse;

class AuthService
{
    private AuthRepository $authRepo;

    public function __construct(AuthRepository $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function register(array $data)
    {
        return $this->authRepo->createUser($data);
    }

    public function login(array $data): JsonResponse
    {
        $user = $this->authRepo->findByEmail($data['email']);

        if (! $user || ! $this->authRepo->validatePassword($user, $data['password'])) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = $this->authRepo->refreshAccessKey($user);

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'access_key' => $user->access_key,
        ]);
    }

    public function refreshAccessKey($user)
    {
        return $this->authRepo->refreshAccessKey($user);
    }

    public function logout($user)
    {
        $this->authRepo->refreshAccessKey($user); // invalidate old key
    }
}