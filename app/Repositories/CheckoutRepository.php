<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckoutRepository
{
    public function createOrder($userId, array $products): Order
    {
        return DB::transaction(function () use ($userId, $products) {
            $order = Order::create([
                'user_id' => $userId,
                'status'  => 'PENDING',
                'total_amount' => 0, // pakai total_amount, bukan total
                'external_invoice_id' => 'ORDER-' . uniqid(),
            ]);

            $total = 0;

            $order->update([
                'total_amount' => $total,
                'external_invoice_id' => 'ORDER-' . $order->id,
            ]);

            foreach ($products as $p) {
                $product = Product::findOrFail($p['id']);

                if ($product->stock < $p['quantity']) {
                    throw new \Exception("Stock not available for {$product->title}");
                }

                $subtotal = $product->price * $p['quantity'];
                $total += $subtotal;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $p['quantity'],
                    'price'      => $product->price,
                ]);

                // Kurangi stok
                $product->decrement('stock', $p['quantity']);
            }

            $order->update(['total_amount' => $total]); // update total_amount

            return $order->fresh('items.product', 'payment');
        });
    }
}