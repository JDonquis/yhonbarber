<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('closings', function (Blueprint $table) {
            $table->id();
            $table->enum('period_type', ['diario', 'semanal', 'mensual']);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_services_usd', 12, 2)->default(0);
            $table->decimal('total_products_usd', 12, 2)->default(0);
            $table->decimal('total_usd', 12, 2)->default(0);
            $table->decimal('total_ves', 16, 2)->default(0);
            $table->decimal('barber_commission_usd', 12, 2)->default(0);
            $table->decimal('shop_amount_usd', 12, 2)->default(0);
            $table->unsignedInteger('ticket_count')->default(0);
            $table->decimal('exchange_rate', 14, 4)->default(0);
            $table->json('details')->nullable();
            $table->enum('status', ['abierto', 'cerrado'])->default('abierto');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['period_type', 'period_start', 'period_end'], 'closings_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closings');
    }
};
