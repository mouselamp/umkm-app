<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'position',
    ];

    public function wages(): HasMany
    {
        return $this->hasMany(Wage::class);
    }
}
