<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'product'  => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'price'    => $this->price,
        ];
    }
}