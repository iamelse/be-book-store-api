<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;

class PaymentRepository
{
    public function createPayment(Order $order, string $method): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'method'   => $method,
            'status'   => 'PENDING',
        ]);
    }

    public function updatePaymentStatus(int $orderId, string $status): void
    {
        $payment = Payment::where('order_id', $orderId)->first();

        if ($payment) {
            $payment->update(['status' => $status]);
            $payment->order->update(['status' => $status === 'PAID' ? 'PAID' : 'FAILED']);
        }
    }
}