<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait APIResponse
{
    public static function successResponse($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function errorResponse(string $message, int $status = 400, $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'status'  => $status,
            'meta' => [
                'timestamp'    => now()->toISOString(),
                'request_id'   => request()->header('X-Request-ID') ?? uniqid(),
            ]
        ], $status);
    }
}