<?php

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
        $this->setAllowedPeriodTypes(['diario', 'semanal', 'mensual', 'rango']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->setAllowedPeriodTypes(['diario', 'semanal', 'mensual']);
    }

    /**
     * Rebuild the period_type column with the given allowed values.
     *
     * The column is recreated (instead of modified in place) so the migration
     * works both on MySQL and on the SQLite enum CHECK constraint.
     *
     * @param  array<int, string>  $values
     */
    protected function setAllowedPeriodTypes(array $values): void
    {
        Schema::table('closings', function (Blueprint $table) use ($values) {
            $table->enum('period_type_tmp', $values)->default($values[0]);
        });

        DB::table('closings')->update(['period_type_tmp' => DB::raw('period_type')]);

        Schema::table('closings', function (Blueprint $table) {
            $table->dropColumn('period_type');
        });

        Schema::table('closings', function (Blueprint $table) {
            $table->renameColumn('period_type_tmp', 'period_type');
        });
    }
};
