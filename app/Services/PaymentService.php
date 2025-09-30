<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\PaymentRepository;

class PaymentService
{
    public function __construct(
        private PaymentRepository $payments,
        private PaymentGatewayFactory $factory
    ) {}

    public function initiatePayment(Order $order, string $method): array|object
    {
        // pilih gateway sesuai method
        $gateway = $this->factory->make($method);

        return $gateway->createTransaction($order);
    }

    public function handleWebhook(string $orderId, string $status): void
    {
        // kalau webhook dari gateway generik (kayak Xendit yang cuma kasih status)
        $order = Order::findOrFail($orderId);

        $this->payments->updatePaymentStatus($order->id, $status);
    }
}