<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\PaymentRepository;

class PaymentService
{
    private PaymentRepository $paymentRepo;

    public function __construct(PaymentRepository $paymentRepo)
    {
        $this->paymentRepo = $paymentRepo;
    }

    public function initiatePayment(Order $order, string $method)
    {
        return $this->paymentRepo->createPayment($order, $method);
    }

    public function handleWebhook(int $orderId, string $status): void
    {
        $this->paymentRepo->updatePaymentStatus($orderId, $status);
    }
}