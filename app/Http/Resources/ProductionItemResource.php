<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'name' => $this->product->name,
            ]),
            'recipe' => $this->whenLoaded('recipe', fn() => [
                'id' => $this->recipe->id,
                'name' => $this->recipe->name,
                'product' => $this->recipe->product ? [
                    'id' => $this->recipe->product->id,
                    'name' => $this->recipe->product->name,
                ] : null,
            ]),
            'quantity' => $this->quantity,
            'cost' => $this->cost,
        ];
    }
}
