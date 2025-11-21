<?php

use App\Enums\RoleEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        
        Route::middleware('auth:api')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

    Route::get('items', [ItemController::class, 'index']);
    Route::get('items/{id}', [ItemController::class, 'show']);

    Route::middleware(['auth:api', 'role:' . RoleEnum::CUSTOMER])->group(function () {
        Route::post('items/{productId}/order', [OrderController::class, 'storeFromProduct']);

        Route::get('cart', [CartController::class, 'index']);
        Route::post('cart/items', [CartController::class, 'add']);
        Route::put('cart/items/{id}', [CartController::class, 'update']);
        Route::delete('cart/items/{id}', [CartController::class, 'remove']);

        Route::post('orders/from-cart', [OrderController::class, 'storeFromCart']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{id}', [OrderController::class, 'show']);

        Route::post('payments', [PaymentController::class, 'create']);
    });

    Route::post('payments/webhook/midtrans', [PaymentController::class, 'webhookMidtrans']);
    Route::post('payments/webhook/xendit', [PaymentController::class, 'webhookXendit']);
});