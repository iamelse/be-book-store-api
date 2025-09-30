<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Throwable;

class MidtransGatewayService implements PaymentGatewayInterface
{
    public function __construct(
        protected PaymentRepository $payments
    ) {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function createTransaction(Order $order): array|object
    {
        try {
            $order->loadMissing('items.product', 'user');

            $params = [
                'transaction_details' => [
                    // Midtrans butuh unique order_id, tambahin timestamp
                    'order_id'     => 'ORDER-' . $order->id . '-' . now()->timestamp,
                    'gross_amount' => (float) $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->user->name ?? 'Customer',
                    'email'      => $order->user->email ?? 'customer@example.com',
                ],
                'item_details' => $order->items->map(fn ($item) => [
                    'id'       => $item->product_id,
                    'price'    => (float) $item->price,
                    'quantity' => (int) $item->quantity,
                    'name'     => $item->product->name ?? 'Product '.$item->product_id,
                ])->toArray(),
            ];

            $transaction = Snap::createTransaction($params);

            Log::info('Midtrans transaction created', [
                'order_id'    => $order->id,
                'params'      => $params,
                'transaction' => $transaction,
            ]);

            return $transaction;
        } catch (Throwable $e) {
            Log::error('Midtrans createTransaction error', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function handleCallback(array $payload): void
    {
        try {
            Log::info('Midtrans webhook payload received', $payload);

            $midtransStatus = $payload['transaction_status'] ?? 'failed';
            $orderIdRaw     = $payload['order_id'] ?? null;

            if (! $orderIdRaw) {
                Log::warning('Callback tanpa order_id', $payload);
                return;
            }

            // Extract internalId (ORDER-{id}-{ts})
            $parts      = explode('-', $orderIdRaw);
            $internalId = $parts[1] ?? null;

            if (! $internalId || ! $order = Order::find((int) $internalId)) {
                Log::warning('Order tidak ditemukan', [
                    'order_id_raw' => $orderIdRaw,
                    'internal_id'  => $internalId,
                ]);
                return;
            }

            $statusMap = [
                'capture'    => 'PAID',
                'settlement' => 'PAID',
                'pending'    => 'PENDING',
                'deny'       => 'FAILED',
                'cancel'     => 'FAILED',
                'expire'     => 'FAILED',
            ];

            $internalStatus = $statusMap[$midtransStatus] ?? 'FAILED';

            $this->payments->updatePaymentStatus($order->id, $internalStatus);

            Log::info('Payment updated', [
                'order_id'        => $order->id,
                'midtrans_status' => $midtransStatus,
                'internal_status' => $internalStatus,
            ]);
        } catch (\Throwable $e) {
            Log::error('Callback error', [
                'error'   => $e->getMessage(),
                'payload' => $payload,
            ]);
        }
    }
}