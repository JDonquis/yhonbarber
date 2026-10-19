<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('closing_sale', function (Blueprint $table) {
            $table->foreignId('closing_id')->constrained('closings')->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->unique(['closing_id', 'sale_id']);
        });

        $this->backfillClosedClosings();
    }

    /**
     * Mark the sales included in already closed closings as settled.
     */
    protected function backfillClosedClosings(): void
    {
        DB::table('closings')
            ->where('status', 'cerrado')
            ->orderBy('id')
            ->chunkById(100, function ($closings) {
                foreach ($closings as $closing) {
                    $start = Carbon::parse($closing->period_start)->startOfDay();
                    $end = Carbon::parse($closing->period_end)->endOfDay();

                    $saleIds = DB::table('sales')
                        ->where('status', 'completada')
                        ->whereBetween('sold_at', [$start->toDateTimeString(), $end->toDateTimeString()])
                        ->when(
                            $closing->barber_id,
                            fn ($query) => $query->where('barber_id', $closing->barber_id),
                            fn ($query) => $query->whereNotNull('barber_id'),
                        )
                        ->pluck('id');

                    if ($saleIds->isEmpty()) {
                        continue;
                    }

                    $rows = $saleIds->map(fn ($saleId) => [
                        'closing_id' => $closing->id,
                        'sale_id' => $saleId,
                    ])->all();

                    DB::table('closing_sale')->insertOrIgnore($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_sale');
    }
};
