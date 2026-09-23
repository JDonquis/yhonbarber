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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('barber_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['servicio', 'producto', 'mixto'])->default('servicio');
            $table->decimal('exchange_rate', 14, 4)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('total_usd', 12, 2)->default(0);
            $table->decimal('total_ves', 16, 2)->default(0);
            $table->decimal('barber_commission_usd', 12, 2)->default(0);
            $table->decimal('shop_amount_usd', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->enum('payment_currency', ['USD', 'VES'])->default('USD');
            $table->enum('status', ['completada', 'anulada'])->default('completada');
            $table->text('notes')->nullable();
            $table->dateTime('sold_at');
            $table->timestamps();

            $table->index('sold_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
