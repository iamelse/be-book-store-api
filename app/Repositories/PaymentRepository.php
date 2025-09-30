<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PaymentRepository
{
    public function createPayment(Order $order, string $gateway, array $meta = []): Payment
    {
        return Payment::create([
            'order_id'       => $order->id,
            'transaction_id' => null,
            'gateway'        => $gateway,
            'status'         => 'PENDING',
            'meta'           => json_encode($meta),
        ]);
    }

    public function updatePaymentMeta(int $paymentId, array $meta): void
    {
        $payment = Payment::find($paymentId);
        if ($payment) {
            $payment->update(['meta' => $meta]);
        }
    }

    public function updatePaymentStatus(int $orderId, string $status): void
    {
        $payment = Payment::where('order_id', $orderId)->first();

        Log::info('Midtrans callback received', [
            'order_id_raw'    => $orderId,
            'internal_status' => $status,
            'payload'         => $payment,
        ]);

        if ($payment) {
            $payment->update([
                'paid_at' => $status === 'PAID' ? now() : null,
                'status' => $status,
                'transaction_id' => 'ORDER-' . $orderId
            ]);
            $payment->order->update(['status' => $status === 'PAID' ? 'PAID' : 'FAILED']);
        }
    }
}