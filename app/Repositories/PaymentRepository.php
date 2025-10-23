<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    public function create(array $data)
    {
        return Payment::create($data);
    }

    public function findByReference(string $paymentReference)
    {
        return Payment::where('payment_reference', $paymentReference)->first();
    }

    public function findPendingByOrder(int $orderId, string $gateway)
    {
        return Payment::where('order_id', $orderId)
            ->where('gateway', $gateway)
            ->where('status', 'pending')
            ->first();
    }

    public function updateStatus(string $paymentReference, string $status, array $meta = [])
    {
        $payment = $this->findByReference($paymentReference);
        if ($payment) {
            $payment->update([
                'payment_method' => $meta['payment_method'] ?? null,
                'status' => $status,
                'meta' => $meta,
            ]);
        }
        return $payment;
    }
}