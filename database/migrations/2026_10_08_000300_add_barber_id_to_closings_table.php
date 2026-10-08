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
            $table->dropUnique('closings_period_unique');
        });

        Schema::table('closings', function (Blueprint $table) {
            $table->foreignId('barber_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('closings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('barber_id');
        });

        Schema::table('closings', function (Blueprint $table) {
            $table->unique(['period_type', 'period_start', 'period_end'], 'closings_period_unique');
        });
    }
};
