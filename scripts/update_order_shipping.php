<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderId = isset($argv[1]) ? intval($argv[1]) : null;
$json = isset($argv[2]) ? $argv[2] : null;

if (! $orderId) {
    echo "Usage: php scripts/update_order_shipping.php <order_id> '{\"name\":...}'\n";
    exit(1);
}

if ($json) {
    $data = @json_decode($json, true);
    if (!is_array($data)) {
        echo "Invalid JSON payload\n";
        exit(1);
    }
} else {
    // defaults (as discussed)
    $data = ['name' => 'Jose Mendoza', 'dni' => '47247322', 'ruc' => null, 'address' => 'av pajares', 'currency' => 'PEN'];
}

\Illuminate\Support\Facades\DB::table('orders')->where('id', $orderId)->update(['shipping_address' => json_encode($data), 'updated_at' => now()]);
echo "Updated order {$orderId} shipping_address to: \n" . json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . "\n";
