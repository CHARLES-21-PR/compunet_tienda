<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Minimal change: try to set ENUM allowed values and default.
        // Note: changing column type requires `doctrine/dbal` for many drivers; if it fails we fallback
        // to updating existing rows to a safe default.
        try {
            Schema::table('orders', function (Blueprint $table) {
                $table->enum('status', ['pagado','entregado','cancelado','fallido'])->default('pagado')->change();
            });
        } catch (\Throwable $e) {
            DB::table('orders')->whereNull('status')->orWhere('status', '')->update(['status' => 'pagado']);
            logger()->warning('Migration update_orders_status_constraints: could not apply enum change; existing rows updated to pagado.');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: set default back to 'pending' and remove constraint when possible
        try {
            $driver = DB::connection()->getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (\Throwable $e) {
            $driver = config('database.default');
        }

        if (in_array($driver, ['mysql','pdo_mysql'])) {
            DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` VARCHAR(191) NOT NULL DEFAULT 'pending';");
        } elseif (in_array($driver, ['pgsql','postgres','pgsql'])) {
            DB::statement("ALTER TABLE orders ALTER COLUMN status SET DEFAULT 'pending';");
            try { DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check;"); } catch (\Throwable $e) {}
        } else {
            try {
                Schema::table('orders', function (Blueprint $table) {
                    $table->string('status')->default('pending')->change();
                });
            } catch (\Throwable $e) {
                logger()->warning('Migration rollback update_orders_status_constraints: could not revert column definition on driver ' . ($driver ?? 'unknown') . '.');
            }
        }
    }
};
