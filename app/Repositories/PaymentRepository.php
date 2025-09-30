<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentRepository
{
    public function createPayment(Order $order, string $gateway, array $meta = []): Payment
    {
        try {
            $payment = Payment::create([
                'order_id'       => $order->id,
                'transaction_id' => null,
                'gateway'        => $gateway,
                'status'         => 'PENDING',
                'meta'           => $meta,
            ]);

            Log::info('Payment created', [
                'order_id' => $order->id,
                'gateway'  => $gateway,
                'payment'  => $payment->id,
            ]);

            return $payment;
        } catch (Throwable $e) {
            Log::error('Payment create error', [
                'order_id' => $order->id,
                'gateway'  => $gateway,
                'message'  => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function updatePaymentMeta(int $paymentId, array $meta): void
    {
        try {
            $payment = Payment::find($paymentId);
            if ($payment) {
                $payment->update(['meta' => $meta]);
                Log::info('Payment meta updated', [
                    'payment_id' => $paymentId,
                    'meta'       => $meta,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Payment meta update error', [
                'payment_id' => $paymentId,
                'message'    => $e->getMessage(),
            ]);
        }
    }

    public function updatePaymentStatus(int $orderId, string $status): void
    {
        try {
            $payment = Payment::where('order_id', $orderId)->first();

            if ($payment) {
                $payment->update([
                    'paid_at'       => $status === 'PAID' ? now() : null,
                    'status'        => $status,
                    'transaction_id'=> 'ORDER-' . $orderId,
                ]);

                $payment->order->update([
                    'status' => $status === 'PAID' ? 'PAID' : 'FAILED',
                ]);

                Log::info('Payment status updated', [
                    'order_id'       => $orderId,
                    'payment_id'     => $payment->id,
                    'new_status'     => $status,
                ]);
            } else {
                Log::warning('Payment not found when updating status', [
                    'order_id' => $orderId,
                    'status'   => $status,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Payment status update error', [
                'order_id' => $orderId,
                'message'  => $e->getMessage(),
            ]);
        }
    }
}