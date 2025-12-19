<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CapitalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'capital_date' => $this->capital_date?->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'source' => $this->source,
            'description' => $this->description,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
