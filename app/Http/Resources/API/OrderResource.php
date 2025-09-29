<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'status' => $this->status,
            'total'  => $this->total,
            'items'  => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}