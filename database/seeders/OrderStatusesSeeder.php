<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderStatusesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('order_statuses')) {
            // If the table doesn't exist (we may have removed it), do nothing.
            return;
        }
        $defaults = [
            ['key' => 'paid', 'label' => 'Pagado', 'meta' => null],
            ['key' => 'delivered', 'label' => 'Entregado', 'meta' => null],
            ['key' => 'cancelled', 'label' => 'Cancelado', 'meta' => null],
            ['key' => 'failed', 'label' => 'Fallido', 'meta' => null],
        ];

        foreach ($defaults as $d) {
            $exists = DB::table('order_statuses')->where('key', $d['key'])->first();
            if (! $exists) {
                DB::table('order_statuses')->insert(array_merge($d, ['created_at' => now(), 'updated_at' => now()]));
            }
        }
    }
}
