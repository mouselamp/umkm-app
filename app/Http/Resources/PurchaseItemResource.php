<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'material' => $this->whenLoaded('material', fn() => [
                'id' => $this->material->id,
                'name' => $this->material->name,
                'unit' => $this->material->unit?->symbol,
            ]),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'expiry_date' => $this->expiry_date,
        ];
    }
}
