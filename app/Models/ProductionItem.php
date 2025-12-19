<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionItem extends Model
{
    protected $fillable = [
        'production_id',
        'recipe_id',
        'quantity',
        'cost',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'cost' => 'decimal:2',
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    public function product(): BelongsTo
    {
        // Product is accessed via recipe relation
        return $this->recipe ? $this->recipe->product() : null;
    }

    // Accessor to get product via recipe
    public function getProductAttribute()
    {
        return $this->recipe?->product;
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
