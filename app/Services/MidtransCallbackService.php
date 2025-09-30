<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\PaymentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransCallbackService
{
    protected PaymentRepository $payments;

    protected array $statusMap = [
        'capture'    => 'PAID',
        'settlement' => 'PAID',
        'pending'    => 'PENDING',
        'deny'       => 'FAILED',
        'cancel'     => 'FAILED',
        'expire'     => 'FAILED',
    ];

    public function __construct(PaymentRepository $payments)
    {
        $this->payments = $payments;
    }

    public function handle(Request $request): void
    {
        $midtransStatus = $request->transaction_status;
        $orderIdRaw     = $request->order_id; // "ORDER-6"

        Log::info('Midtrans callback received', [
            'order_id_raw'    => $orderIdRaw,
            'midtrans_status' => $midtransStatus,
            'payload'         => $request->all(),
        ]);

        // cari order internal berdasarkan external_invoice_id
        $order = Order::where('external_invoice_id', $orderIdRaw)->first();

        if (! $order) {
            Log::warning('Order not found for Midtrans callback', [
                'order_id_raw' => $orderIdRaw,
            ]);
            return;
        }

        $internalStatus = $this->mapStatus($midtransStatus);

        $this->payments->updatePaymentStatus($order->id, $internalStatus);

        Log::info('Midtrans callback processed', [
            'order_id_raw'      => $orderIdRaw,
            'order_id_internal' => $order->id,
            'midtrans_status'   => $midtransStatus,
            'internal_status'   => $internalStatus,
        ]);
    }

    protected function mapStatus(string $status): string
    {
        return $this->statusMap[$status] ?? 'FAILED';
    }
}