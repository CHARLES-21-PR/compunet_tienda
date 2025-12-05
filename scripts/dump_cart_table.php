<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$items = DB::table('shoppings_carts')->get();

echo "Total items in shoppings_carts: " . $items->count() . "\n";

foreach ($items as $item) {
    echo "ID: {$item->id} | User: {$item->user_id} | Product: {$item->product_id} | Qty: {$item->quantity} | Price: {$item->price}\n";
    
    $prod = DB::table('products')->where('id', $item->product_id)->first();
    if ($prod) {
        echo "   -> Product Price in DB: {$prod->price}\n";
    } else {
        echo "   -> Product NOT FOUND\n";
    }
}
