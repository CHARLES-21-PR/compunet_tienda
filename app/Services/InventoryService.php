<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Check if requested quantity can be fulfilled
     */
    public function canReserve(Product $product, int $requestedQuantity): bool
    {
        if ($product->stock === null) return true; // unlimited
        return $requestedQuantity <= intval($product->stock);
    }

    public function available(Product $product): int
    {
        return $product->stock === null ? PHP_INT_MAX : intval($product->stock);
    }

    /**
     * Decrease product stock by quantity. Returns true on success.
     */
    public function decreaseStock(Product $product, int $quantity): bool
    {
        if ($product->stock === null) return true; // unlimited

        // Use a transaction / optimistic check to avoid race conditions
        return DB::transaction(function() use ($product, $quantity) {
            $p = Product::lockForUpdate()->find($product->id);
            if (! $p) return false;
            $current = intval($p->stock);
            if ($quantity > $current) return false;
            $p->stock = $current - $quantity;
            return $p->save();
        });
    }

    /**
     * Increase stock (useful for rollbacks)
     */
    public function increaseStock(Product $product, int $quantity): bool
    {
        if ($product->stock === null) return true;
        return DB::transaction(function() use ($product, $quantity) {
            $p = Product::lockForUpdate()->find($product->id);
            if (! $p) return false;
            $p->stock = intval($p->stock) + $quantity;
            return $p->save();
        });
    }
}
