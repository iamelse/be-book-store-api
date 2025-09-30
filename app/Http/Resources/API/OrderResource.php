<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'status'       => $this->status,
            'total_amount' => $this->total_amount,
            'items'        => OrderItemResource::collection($this->items),
            'payment' => [
                'gateway'        => $this->payment->gateway ?? null,
                'status'         => $this->payment->status ?? null,
                'transaction_id' => $this->payment->transaction_id ?? null,
                'paid_at'        => $this->payment->paid_at,
                'meta'           => $this->payment->meta ?? null,
            ],
        ];
    }
}