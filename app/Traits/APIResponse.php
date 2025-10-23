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
            'data' => $data,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-ID') ?? uniqid(),
            ]
        ], $status);
    }

    public static function errorResponse(string $message, int $status = 400, $errors = [], string $code = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => $code ?? self::resolveErrorCode($status),
            'meta' => [
                'timestamp' => now()->toISOString(),
                'request_id' => request()->header('X-Request-ID') ?? uniqid(),
            ]
        ], $status);
    }

    private static function resolveErrorCode(int $status): string
    {
        return match ($status) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            422 => 'VALIDATION_ERROR',
            500 => 'SERVER_ERROR',
            default => 'ERROR',
        };
    }
}