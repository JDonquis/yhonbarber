<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory;

    public const TYPE_SERVICE = 'servicio';

    public const TYPE_PRODUCT = 'producto';

    protected $fillable = [
        'sale_id',
        'item_type',
        'service_id',
        'product_id',
        'name',
        'quantity',
        'unit_price_usd',
        'line_total_usd',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_usd' => 'decimal:2',
        'line_total_usd' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
