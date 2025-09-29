<?php

namespace App\Services;

use App\Repositories\OrderRepository;

class OrderService
{
    private OrderRepository $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function getUserOrders(int $userId)
    {
        return $this->orderRepo->getOrdersByUser($userId);
    }

    public function getOrder(int $id, int $userId)
    {
        return $this->orderRepo->findById($id, $userId);
    }
}