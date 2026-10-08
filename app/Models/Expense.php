<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'concept',
        'amount_usd',
        'expense_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'amount_usd' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereDate('expense_date', '>=', $start)
            ->whereDate('expense_date', '<=', $end);
    }
}
