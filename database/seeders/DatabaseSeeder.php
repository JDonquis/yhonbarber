<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@yhonbarber.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'active' => true,
                'email_verified_at' => now(),
            ],
        );

        $barber = User::updateOrCreate(
            ['email' => 'barbero@yhonbarber.com'],
            [
                'name' => 'Carlos Barbero',
                'password' => Hash::make('password'),
                'role' => User::ROLE_BARBER,
                'active' => true,
                'phone' => '0412-0000000',
                'email_verified_at' => now(),
            ],
        );

        foreach ([
            ['name' => 'Corte clásico', 'description' => 'Corte de cabello tradicional', 'price' => 5.00],
            ['name' => 'Degradado / Fade', 'description' => 'Corte degradado a máquina', 'price' => 6.00],
            ['name' => 'Corte + barba', 'description' => 'Corte de cabello más arreglo de barba', 'price' => 8.00],
            ['name' => 'Arreglo de barba', 'description' => 'Perfilado y arreglo de barba', 'price' => 3.00],
            ['name' => 'Corte para niño', 'description' => 'Corte de cabello infantil', 'price' => 4.00],
        ] as $service) {
            Service::updateOrCreate(['name' => $service['name']], $service + ['active' => true]);
        }

        $products = [
            ['name' => 'Cera para cabello', 'sku' => 'CER-001', 'price' => 7.00, 'cost' => 4.00, 'stock' => 15, 'min_stock' => 5],
            ['name' => 'Shampoo anticaída', 'sku' => 'SHA-001', 'price' => 9.00, 'cost' => 5.50, 'stock' => 10, 'min_stock' => 3],
            ['name' => 'Aceite para barba', 'sku' => 'ACE-001', 'price' => 8.50, 'cost' => 5.00, 'stock' => 8, 'min_stock' => 3],
            ['name' => 'Gel fijador', 'sku' => 'GEL-001', 'price' => 6.00, 'cost' => 3.50, 'stock' => 12, 'min_stock' => 4],
            ['name' => 'Talco perfumado', 'sku' => 'TAL-001', 'price' => 4.00, 'cost' => 2.00, 'stock' => 2, 'min_stock' => 3],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(['sku' => $data['sku']], $data + ['active' => true]);

            if ($product->wasRecentlyCreated) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $admin->id,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $product->stock,
                    'stock_after' => $product->stock,
                    'note' => 'Inventario inicial',
                ]);
            }
        }

        foreach ([
            'shop_name' => 'YhonBarber',
            'shop_rif' => '',
            'shop_phone' => '',
            'shop_address' => '',
            'commission_rate' => '40',
            'payment_methods' => 'Efectivo Bs, Efectivo USD, Pago móvil, Punto de venta, Transferencia, Zelle',
            'dolar_api_source' => 'oficial',
            'exchange_rate_manual' => '',
            'exchange_rate_fallback' => '',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
