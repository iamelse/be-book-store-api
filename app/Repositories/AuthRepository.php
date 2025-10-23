<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);
    }

    /**
     * Authenticate user and return JWT token.
     */
    public function findUserByCredentials(array $credentials): ?string
    {
        $token = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ]);

        return $token ?: null;
    }

    /**
     * Get the authenticated user.
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Log out the user (invalidate token).
     */
    public function logoutUser(): void
    {
        Auth::logout();
    }

    /**
     * Refresh JWT token.
     */
    public function refresh(): array
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Format JWT response.
     */
    public function respondWithToken(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'user' => Auth::user(),
        ];
    }
}