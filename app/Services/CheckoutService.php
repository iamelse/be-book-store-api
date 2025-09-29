<?php

namespace App\Services;

use App\Repositories\CheckoutRepository;

class CheckoutService
{
    private CheckoutRepository $checkoutRepo;

    public function __construct(CheckoutRepository $checkoutRepo)
    {
        $this->checkoutRepo = $checkoutRepo;
    }

    public function checkout(int $userId, array $products)
    {
        return $this->checkoutRepo->createOrder($userId, $products);
    }
}