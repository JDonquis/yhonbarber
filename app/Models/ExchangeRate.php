<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'rate',
        'fetched_at',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'fetched_at' => 'datetime',
    ];
}
