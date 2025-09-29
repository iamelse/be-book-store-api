<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function getOrdersByUser(int $userId): Collection
    {
        return Order::with('items.product')->where('user_id', $userId)->get();
    }

    public function findById(int $id, int $userId): ?Order
    {
        return Order::with('items.product')->where('id', $id)->where('user_id', $userId)->first();
    }
}