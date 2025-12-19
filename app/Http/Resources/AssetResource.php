<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
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
            'name' => $this->name,
            'asset_number' => $this->asset_number,
            'purchase_date' => $this->purchase_date?->format('Y-m-d'),
            'purchase_price' => (float) $this->purchase_price,
            'useful_life_month' => $this->useful_life_month,
            'residual_value' => (float) $this->residual_value,
            'book_value' => (float) $this->book_value,
            'payment_type' => $this->payment_type,
            'status' => $this->status,
            'monthly_depreciation' => $this->getMonthlyDepreciation(),
            'depreciations' => $this->whenLoaded('depreciations', fn() => $this->depreciations->map(fn($d) => [
                'id' => $d->id,
                'period' => $d->period?->format('Y-m'),
                'amount' => (float) $d->amount,
                'accumulated' => (float) $d->accumulated,
                'book_value_after' => (float) $d->book_value_after,
            ])),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    private function getMonthlyDepreciation(): float
    {
        if ($this->useful_life_month <= 0) return 0;
        return ($this->purchase_price - $this->residual_value) / $this->useful_life_month;
    }
}
