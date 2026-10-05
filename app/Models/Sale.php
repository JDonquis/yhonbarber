<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    public const STATUS_COMPLETED = 'completada';

    public const STATUS_CANCELLED = 'anulada';

    public const TYPE_SERVICE = 'servicio';

    public const TYPE_PRODUCT = 'producto';

    public const TYPE_MIXED = 'mixto';

    protected $fillable = [
        'code',
        'user_id',
        'barber_id',
        'type',
        'exchange_rate',
        'commission_rate',
        'total_usd',
        'total_ves',
        'barber_commission_usd',
        'shop_amount_usd',
        'payment_method',
        'payment_currency',
        'status',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'commission_rate' => 'decimal:2',
        'total_usd' => 'decimal:2',
        'total_ves' => 'decimal:2',
        'barber_commission_usd' => 'decimal:2',
        'shop_amount_usd' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function barber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'barber_id')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeBetween($query, $start, $end)
    {
        return $query->whereBetween('sold_at', [$start, $end]);
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
