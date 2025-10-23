<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    /**
     * Ambil semua order milik user
     */
    public function getUserOrders($userId)
    {
        return Order::with('orderItems')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Buat order baru dari data yang diterima
     */
    public function createOrder($userId, array $items, array $extraData)
    {
        return DB::transaction(function () use ($userId, $items, $extraData) {
            $totalAmount = collect($items)->sum(fn($i) => $i['price'] * $i['quantity']);

            // Data default
            $baseData = [
                'user_id' => $userId,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Gabungkan dengan data tambahan (address, note, dll)
            $orderData = array_merge($baseData, $extraData);

            $order = Order::create($orderData);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            return $order->load('orderItems');
        });
    }

    /**
     * Ambil detail satu order milik user
     */
    public function getOrderById($userId, $orderId)
    {
        return Order::with('orderItems')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();
    }
}