<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Depreciation extends Model
{
    protected $fillable = [
        'asset_id',
        'period',
        'amount',
        'accumulated',
        'book_value_after',
    ];

    protected $casts = [
        'period' => 'date',
        'amount' => 'decimal:2',
        'accumulated' => 'decimal:2',
        'book_value_after' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'reference');
    }
}
