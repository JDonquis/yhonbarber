<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Closing extends Model
{
    use HasFactory;

    public const PERIOD_DAILY = 'diario';

    public const PERIOD_WEEKLY = 'semanal';

    public const PERIOD_MONTHLY = 'mensual';

    public const STATUS_OPEN = 'abierto';

    public const STATUS_CLOSED = 'cerrado';

    protected $fillable = [
        'barber_id',
        'period_type',
        'period_start',
        'period_end',
        'total_services_usd',
        'total_products_usd',
        'total_usd',
        'total_ves',
        'total_ves_reference',
        'barber_commission_usd',
        'total_expenses_usd',
        'shop_amount_usd',
        'ticket_count',
        'exchange_rate',
        'average_rate',
        'details',
        'status',
        'closed_by',
        'closed_at',
        'reopened_by',
        'reopened_at',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_services_usd' => 'decimal:2',
        'total_products_usd' => 'decimal:2',
        'total_usd' => 'decimal:2',
        'total_ves' => 'decimal:2',
        'total_ves_reference' => 'decimal:2',
        'barber_commission_usd' => 'decimal:2',
        'total_expenses_usd' => 'decimal:2',
        'shop_amount_usd' => 'decimal:2',
        'ticket_count' => 'integer',
        'exchange_rate' => 'decimal:4',
        'average_rate' => 'decimal:4',
        'details' => 'array',
        'closed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    public function barber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'barber_id')->withTrashed();
    }

    public function isForBarber(): bool
    {
        return $this->barber_id !== null;
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by')->withTrashed();
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by')->withTrashed();
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
