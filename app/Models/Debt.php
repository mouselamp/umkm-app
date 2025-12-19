<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Debt extends Model
{
    protected $fillable = [
        'supplier_id',
        'debt_type',
        'reference_type',
        'reference_id',
        'debt_date',
        'amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'debt_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
