<?php

namespace App\Contracts;

use App\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Buat transaksi baru (checkout).
     */
    public function createTransaction(Order $order): array|object;

    /**
     * Handle callback / webhook dari gateway.
     */
    public function handleCallback(array $payload): void;
}