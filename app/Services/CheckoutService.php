<?php

namespace App\Services;

use App\Repositories\CheckoutRepository;
use App\Repositories\PaymentRepository;
use App\Services\MidtransService;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private CheckoutRepository $checkoutRepo,
        private PaymentRepository $paymentRepo,
        private MidtransService $midtrans
    ) {}

    public function checkout(int $userId, array $products): Order
    {
        return DB::transaction(function () use ($userId, $products) {
            $order = $this->checkoutRepo->createOrder($userId, $products);

            $meta = [
                'snap_token' => null,
                'redirect_url' => null,
            ];

            $payment = $this->paymentRepo->createPayment($order, 'MIDTRANS', $meta);

            $amount = $order->total_amount ?? 0;

            if ($amount <= 0) {
                return $order->fresh('items.product', 'payment');
            }

            $transaction = $this->midtrans->createTransaction($order);

            $token       = $transaction->token ?? null;
            $redirectUrl = $transaction->redirect_url ?? null;

            $payment->update([
                'meta' => [
                    'snap_token'   => $token,
                    'redirect_url' => $redirectUrl,
                ],
            ]);

            return $order->fresh('items.product', 'payment');
        });
    }
}