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
        Schema::table('closings', function (Blueprint $table) {
            $table->decimal('average_rate', 14, 4)->nullable()->after('exchange_rate');
            $table->decimal('total_ves_reference', 16, 2)->default(0)->after('total_ves');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('closings', function (Blueprint $table) {
            $table->dropColumn(['average_rate', 'total_ves_reference']);
        });
    }
};
