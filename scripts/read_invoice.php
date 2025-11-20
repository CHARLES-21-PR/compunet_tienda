<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderId = isset($argv[1]) ? intval($argv[1]) : null;
if (! $orderId) {
    echo "Usage: php scripts/read_invoice.php <order_id>\n";
    exit(1);
}

$inv = \Illuminate\Support\Facades\DB::table('invoices')->where('order_id', $orderId)->orderBy('id', 'desc')->first();
echo json_encode($inv, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
