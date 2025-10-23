<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function createPayment(string $externalId, int $amount, array $options = []): array;

    public function getPaymentReferenceFromPayload(array $payload): string;

    public function getStatusFromPayload(array $payload): string;
}