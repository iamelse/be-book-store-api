<?php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Create Midtrans transaction from an Order model
     *
     * @param \App\Models\Order $order
     * @return object|array
     * @throws \Exception
     */
    public function createTransaction($order): object|array
    {
        // Load order items if not already loaded
        if (! $order->relationLoaded('items')) {
            $order->load('items.product', 'user'); // load related user and product
        }

        // Hitung total dari semua item
        $totalAmount = $order->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        if ($totalAmount <= 0) {
            throw new \Exception("Order total must be greater than 0");
        }

        // Set payload untuk Midtrans
        $params = [
            'transaction_details' => [
                'order_id'     => 'ORDER-' . $order->id, // pastikan string unik
                'gross_amount' => (float) $order->total_amount,
            ],
            'customer_details' => [
                'first_name' => $order->user->name ?? 'Customer',
                'email'      => $order->user->email ?? 'customer@example.com',
            ],
            'item_details' => $order->items->map(function ($item) {
                return [
                    'id'       => $item->product_id,
                    'price'    => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'name'     => $item->product->name ?? 'Product '.$item->product_id,
                ];
            })->toArray(),
        ];

        // Buat transaksi Midtrans
        return Snap::createTransaction($params);
    }
}