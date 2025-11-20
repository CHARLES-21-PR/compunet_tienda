<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Simple migration: try to change to ENUM including 'pending'. If it fails, set existing empty/null to 'pending'.
        try {
            Schema::table('orders', function (Blueprint $table) {
                // estados en español: pagado, pendiente, fallido, cancelado, entregado
                // NOTA: no establecer valor por defecto aquí; la aplicación decidirá el estado inicial según el método de pago.
                $table->enum('status', ['pagado','pendiente','fallido','cancelado','entregado'])->change();
            });
        } catch (\Throwable $e) {
            // fallback: ensure existing rows have a sensible value
            DB::table('orders')->whereNull('status')->orWhere('status', '')->update(['status' => 'pendiente']);
            logger()->warning('Migration allow_pending_status_orders: could not change enum, existing rows set to pendiente.');
        }
    }

    public function down(): void
    {
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('status')->default('pending')->change();
            });
        } catch (\Throwable $e) {
            logger()->warning('Migration allow_pending_status_orders down: could not revert column.');
        }
    }
};
