<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Database\Seeders\SalesHistorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesHistorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_sales_history_respecting_stock(): void
    {
        (new SalesHistorySeeder)->days(10)->run();

        $this->assertGreaterThan(0, Sale::query()->where('type', Sale::TYPE_SERVICE)->count());
        $this->assertGreaterThan(0, Sale::query()->where('type', Sale::TYPE_PRODUCT)->count());

        $this->assertSame(0, Sale::query()->doesntHave('items')->count());

        $this->assertSame(0, Sale::query()
            ->where('type', Sale::TYPE_PRODUCT)
            ->whereDoesntHave('items', fn ($query) => $query->where('item_type', SaleItem::TYPE_PRODUCT))
            ->count());

        $this->assertSame(0, Product::query()->where('stock', '<', 0)->count());

        $this->assertGreaterThan(0, StockMovement::query()->where('type', StockMovement::TYPE_OUT)->count());
        $this->assertGreaterThan(0, StockMovement::query()->where('type', StockMovement::TYPE_IN)->count());

        $this->assertTrue(
            Sale::query()->min('sold_at') >= now()->subDays(10)->startOfDay()->toDateTimeString(),
        );
    }
}
