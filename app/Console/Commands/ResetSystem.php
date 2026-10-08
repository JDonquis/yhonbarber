<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset {--force : Ejecuta la limpieza sin pedir confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina todos los datos del sistema (ventas, cierres, inventario, etc.) y los barberos, conservando los administradores, la configuración y los correos indicados';

    /**
     * Tablas que serán vaciadas. Los administradores y la configuración se conservan.
     *
     * @var array<int, string>
     */
    protected array $tables = [
        'sale_items',
        'stock_movements',
        'sales',
        'closings',
        'expenses',
        'products',
        'services',
        'exchange_rates',
        'password_reset_requests',
        'password_reset_tokens',
    ];

    /**
     * Correos de barberos que se conservan al reiniciar el sistema.
     *
     * @var array<int, string>
     */
    protected array $keepBarberEmails = [
        'jesuschirinos2003@gmail.com',
        'marlon@gmail.com',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('¿Seguro que deseas reiniciar el sistema? Esta acción no se puede deshacer')) {
            $this->info('Operación cancelada.');

            return self::SUCCESS;
        }

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($this->tables as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                DB::table($table)->truncate();
                $this->line("Vaciada: {$table}");
            }

            User::withTrashed()
                ->where('role', User::ROLE_BARBER)
                ->whereNotIn('email', $this->keepBarberEmails)
                ->get()
                ->each(function (User $user) {
                    $this->line("Barbero eliminado: {$user->email}");
                    $user->forceDelete();
                });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->info('Sistema reiniciado. Se conservaron los administradores, la configuración y los correos indicados.');

        return self::SUCCESS;
    }
}
