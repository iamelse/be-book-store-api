<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CheckoutRequest;
use App\Http\Resources\API\OrderResource;
use App\Services\CheckoutService;

class CheckoutController extends Controller
{
    private CheckoutService $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }

    public function checkout(CheckoutRequest $request)
    {
        $order = $this->checkoutService->checkout(
            $request->user()->id,
            $request->validated()['products']
        );

        return new OrderResource($order);
    }
}