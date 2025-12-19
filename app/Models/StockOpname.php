<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpname extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'reference_id',
        'system_qty',
        'actual_qty',
        'difference',
        'reason',
        'opname_date',
    ];

    protected $casts = [
        'system_qty' => 'decimal:2',
        'actual_qty' => 'decimal:2',
        'difference' => 'decimal:2',
        'opname_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
