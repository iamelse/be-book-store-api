<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment' => [
                'id' => $this->id,
                'order_id' => $this->order_id,
                'gateway' => $this->gateway,
                'external_id' => $this->external_id,
                'payment_reference' => $this->payment_reference,
                'amount' => $this->amount,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'gateway_response' => $this->formatGatewayResponse($this->gateway, $this->gateway_response ?? []),
        ];
    }

    private function formatGatewayResponse(string $gateway, array $response): array
    {
        switch (strtolower($gateway)) {
            case 'midtrans':
                return [
                    'status' => $response['status'] ?? 'pending',
                    'payment_url' => $response['payment_url'] ?? null,
                    'payment_reference' => $response['payment_reference'] ?? null,
                    'midtrans_response' => $response['midtrans_response'] ?? null,
                ];

            case 'xendit':
                return [
                    'status' => $response['status'] ?? 'pending',
                    'invoice_url' => $response['invoice_url'] ?? null,
                    'payment_reference' => $response['payment_reference'] ?? null,
                    'meta' => $response['meta'] ?? null,
                ];

            default:
                return $response;
        }
    }
}