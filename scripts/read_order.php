<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderId = isset($argv[1]) ? intval($argv[1]) : null;
if (! $orderId) {
    echo "Usage: php scripts/read_order.php <order_id>\n";
    exit(1);
}

$row = \Illuminate\Support\Facades\DB::table('orders')->where('id', $orderId)->first();
if (! $row) {
    echo json_encode(['error' => 'order_not_found', 'order_id' => $orderId]) . "\n";
    exit(0);
}

$out = (array)$row;
if (!empty($out['shipping_address'])) {
    $out['shipping_address_parsed'] = @json_decode($out['shipping_address'], true);
}

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
