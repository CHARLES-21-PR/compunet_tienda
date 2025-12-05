<?php

use App\Models\shoppingCart;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate the controller logic
$user = User::where('email', 'admin@admin.com')->first(); // Assuming admin is the user, or pick first user
if (!$user) {
    $user = User::first();
}

echo "User ID: " . $user->id . "\n";

$raw = shoppingCart::where('user_id', $user->id)->with('product')->get();

echo "Cart Items Count: " . $raw->count() . "\n";

foreach ($raw as $it) {
    echo "Cart Item ID: " . $it->id . "\n";
    echo "Cart Item Price (DB attribute): " . $it->getAttributes()['price'] . "\n";
    
    if ($it->product) {
        echo "Product ID: " . $it->product->id . "\n";
        echo "Product Price (Model): " . $it->product->price . "\n";
        
        $rawPrice = DB::table('products')->where('id', $it->product_id)->value('price');
        echo "Product Price (Raw DB): " . $rawPrice . "\n";
    } else {
        echo "No Product Relation\n";
    }
    echo "------------------------\n";
}
