<?php

use App\Models\Product;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = Product::all();

foreach ($products as $product) {
    echo "ID: {$product->id} - Name: {$product->name} - Price: {$product->price}\n";
}
