<?php

namespace App\Services;

use App\Models\Item;
use App\Repositories\OrderRepository;
use App\Repositories\CartRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    protected $orderRepository;
    protected $cartRepository;

    public function __construct(OrderRepository $orderRepository, CartRepository $cartRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * Ambil semua order milik user
     */
    public function getUserOrders($userId)
    {
        return $this->orderRepository->getUserOrders($userId);
    }

    /**
     * Buat pesanan baru dari keranjang
     */
    public function createOrderFromCart($userId, array $data)
    {
        try {
            $cart = $this->cartRepository->getActiveCart($userId);
            $cartItems = $cart ? $cart->cartItems : collect();

            if ($cartItems->isEmpty()) {
                throw new Exception('Keranjang belanja kosong');
            }

            $itemsData = [];

            DB::transaction(function () use ($cartItems, &$itemsData) {
                foreach ($cartItems as $cartItem) {
                    $item = Item::where('id', $cartItem->item_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($item->stock < $cartItem->quantity) {
                        throw new Exception("Stok item {$item->title} tidak cukup");
                    }

                    $item->decrement('stock', $cartItem->quantity);

                    $itemsData[] = [
                        'item_id' => $cartItem->item_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $item->price,
                    ];
                }
            });

            $order = $this->orderRepository->createOrder($userId, $itemsData, $data);

            $this->cartRepository->clearCart($userId);

            return $order;

        } catch (Exception $e) {
            Log::error('Gagal membuat pesanan dari keranjang: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Buat pesanan langsung dari satu produk
     */
    public function createOrderFromProduct($userId, $productId, array $data)
    {
        try {
            return DB::transaction(function () use ($userId, $productId, $data) {
                $quantity = $data['quantity'] ?? 1;
                $item = Item::lockForUpdate()->findOrFail($productId);

                if ($item->stock < $quantity) {
                    throw new Exception("Stok produk {$item->title} tidak cukup");
                }

                $item->decrement('stock', $quantity);

                $itemsData = [[
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->price,
                ]];

                return $this->orderRepository->createOrder($userId, $itemsData, $data);
            });
        } catch (Exception $e) {
            Log::error('Gagal membuat pesanan dari produk: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Ambil detail satu order user
     */
    public function getOrderDetail($userId, $orderId)
    {
        return $this->orderRepository->getOrderById($userId, $orderId);
    }
}