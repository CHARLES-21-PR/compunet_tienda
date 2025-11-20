<!doctype html>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante - {{ $invoiceNumber }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color:#222 }
        .header { text-align: center; margin-bottom: 10px }
        .company { font-weight: bold; font-size: 16px }
        .invoice-meta { margin-top: 6px }
        table { width: 100%; border-collapse: collapse; margin-top: 10px }
        th, td { border: 1px solid #ddd; padding: 6px }
        th { background: #f5f5f5 }
        .right { text-align: right }
        .totals { margin-top: 8px; float: right; width: 40% }
    </style>
</head>
<body>
    <div class="header">
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Comprobante - {{ $invoiceNumber }}</title>
            <style>
                body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color:#222 }
                .header { margin-bottom: 10px }
                .company { font-weight: bold; font-size: 16px; margin-bottom:4px }
                .company small { display:block; font-weight:normal; font-size:11px; color:#333 }
                .receipt-box { border:1px solid #222; padding:8px; display:inline-block; margin-top:8px }
                .receipt-type { font-weight:bold; font-size:14px }
                .meta-row { margin-top:6px }
                .client { margin-top:12px; text-align: left}
                table { width:100%; border-collapse:collapse; margin-top:10px }
                th, td { border:1px solid #ddd; padding:6px }
                th { background:#f5f5f5 }
                .right { text-align:right }
                .totals { margin-top:8px; float:right; width:40% }
            </style>
        </head>
        <body>
            {{-- Empresa: datos verticales --}}
            <div class="header">
                <div class="company">{{ env('ISSUER_NAME', 'Empresa') }}
                    <small>RUC: {{ env('ISSUER_RUC', '') }}</small>
                    <small>{{ env('ISSUER_ADDRESS', '') }}</small>
                </div>

                {{-- Recuadro con tipo, número del comprobante e ID del pedido --}}
                @php
                    $tipo = ($payload['tipo_de_comprobante'] ?? '') == '01' ? 'FACTURA' : 'BOLETA';
                    $issueDate = $payload['issue_date'] ?? date('Y-m-d');
                @endphp
                <div class="receipt-box">
                    <div class="receipt-type">{{ $tipo }}</div>
                    <div class="meta-row">Comprobante: <strong>{{ $invoiceNumber }}</strong></div>
                    <div class="meta-row">Fecha: {{ $issueDate }}</div>
                </div>
            </div>
            <br>

            {{-- Datos del cliente: mostrar etiqueta DNI o RUC según el formato del número --}}
            @php
                // Try multiple sources for client data: payload keys, provider, customer object, order->user
                $clientName = $payload['cliente_denominacion'] ?? ($payload['customer']['name'] ?? ($payload['provider']['cliente_denominacion'] ?? ($order->user->name ?? 'Cliente')));
                $clientDoc = $payload['cliente_numero_de_documento'] ?? ($payload['customer']['doc'] ?? ($payload['provider']['cliente_numero_de_documento'] ?? ($order->user->documento ?? '')));
                $clientAddress = $payload['cliente_direccion'] ?? ($payload['customer']['address'] ?? ($payload['provider']['cliente_direccion'] ?? ($order->user->address ?? '')));
                $docLabel = 'Documento';
                if (!empty($clientDoc) && preg_match('/^\d{11}$/', $clientDoc)) $docLabel = 'RUC';
                elseif (!empty($clientDoc) && preg_match('/^\d{8}$/', $clientDoc)) $docLabel = 'DNI';
            @endphp

            <div class="client">
                <strong>Cliente:</strong> {{ $clientName }}<br>
                <strong>{{ $docLabel }}:</strong> {{ $clientDoc }}<br>
                <strong>Dirección:</strong> {{ $clientAddress }}
            </div>

            {{-- Items --}}
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th>Cantidad</th>
                        <th>Precio unit.</th>
                        <th>Importe</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $items = $payload['items'] ?? [];

                    if (empty($items) && $order) {
                        foreach ($order->items as $it) {
                            $cat = '';
                            try {
                                if (!empty($it->product) && !empty($it->product->category)) {
                                    $cat = $it->product->category->name ?? '';
                                } else {
                                    $cat = $it->category ?? '';
                                }
                            } catch (\Exception $e) {
                                $cat = $it->category ?? '';
                            }
                            $desc = $it->name ?? ($it->description ?? '');
                            $qty = $it->quantity ?? ($it->qty ?? 1);
                            $price = $it->price ?? ($it->unit_price ?? 0);
                            echo "<tr><td>".htmlspecialchars($desc)."</td><td>".htmlspecialchars($cat)."</td><td class=\"right\">{$qty}</td><td class=\"right\">".number_format($price,2,'.','')."</td><td class=\"right\">".number_format($price * $qty,2,'.','')."</td></tr>";
                        }
                    } else {
                        // Recolectar posibles product_ids tanto desde payload items como desde provider.items (codigo)
                        $productIds = [];
                        foreach ($items as $it) {
                            if (!empty($it['product_id'])) $productIds[] = intval($it['product_id']);
                        }
                        $providerItems = $payload['provider']['items'] ?? [];
                        $providerCodesByIndex = [];
                        foreach ($providerItems as $idx => $pit) {
                            if (!empty($pit['codigo'])) {
                                $providerCodesByIndex[$idx] = intval($pit['codigo']);
                                $productIds[] = intval($pit['codigo']);
                            }
                        }

                        $productMap = [];
                        if (!empty($productIds)) {
                            $products = \App\Models\Product::whereIn('id', array_values(array_unique($productIds)))->with('category')->get();
                            foreach ($products as $p) $productMap[$p->id] = $p;
                        }

                        foreach ($items as $index => $it) {
                            $desc = $it['descripcion'] ?? ($it['description'] ?? ($it['name'] ?? ''));
                            $qty = $it['cantidad'] ?? ($it['quantity'] ?? 1);
                            $price = $it['valor_unitario'] ?? ($it['unit_price'] ?? ($it['price'] ?? 0));
                            $cat = $it['categoria'] ?? $it['category'] ?? $it['category_nombre'] ?? '';

                            // Fallback: si no hay categoría, intentar resolver por product_id o por código en provider.items (mismo índice)
                            if (empty($cat)) {
                                $resolved = null;
                                if (!empty($it['product_id'])) {
                                    $pid = intval($it['product_id']);
                                    if (!empty($productMap[$pid]) && !empty($productMap[$pid]->category)) {
                                        $resolved = $productMap[$pid]->category->name ?? null;
                                    }
                                }
                                // intentar por provider item en la misma posición
                                if (empty($resolved) && isset($providerCodesByIndex[$index])) {
                                    $pid2 = intval($providerCodesByIndex[$index]);
                                    if (!empty($productMap[$pid2]) && !empty($productMap[$pid2]->category)) {
                                        $resolved = $productMap[$pid2]->category->name ?? null;
                                    }
                                }
                                // intentar por coincidencia de descripcion entre provider items
                                if (empty($resolved) && !empty($providerItems)) {
                                    foreach ($providerItems as $pit) {
                                        $pdesc = $pit['descripcion'] ?? $pit['descripcion'] ?? '';
                                        if (!empty($pdesc) && !empty($desc) && stripos(trim($pdesc), trim($desc)) !== false) {
                                            if (!empty($pit['codigo']) && !empty($productMap[intval($pit['codigo'])]) && !empty($productMap[intval($pit['codigo'])]->category)) {
                                                $resolved = $productMap[intval($pit['codigo'])]->category->name ?? null;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if (!empty($resolved)) $cat = $resolved;
                            }

                            echo "<tr><td>".htmlspecialchars($desc)."</td><td>".htmlspecialchars($cat)."</td><td class=\"right\">{$qty}</td><td class=\"right\">".number_format($price,2,'.','')."</td><td class=\"right\">".number_format($price * $qty,2,'.','')."</td></tr>";
                        }
                    }
                @endphp
                </tbody>
            </table>

            <div class="totals">
                @php
                    $subtotal = $payload['total_gravada'] ?? ($order->items->sum(function($it){ return $it->price * $it->quantity; }) ?? 0);
                    $igv = $payload['total_igv'] ?? 0;
                    $total = $payload['total'] ?? ($subtotal + $igv);
                @endphp
                <table>
                    <tr><th>Subtotal</th><td class="right">S/ {{ number_format($subtotal,2) }}</td></tr>
                    <tr><th>IGV</th><td class="right">S/ {{ number_format($igv,2) }}</td></tr>
                    <tr><th>Total</th><td class="right"><strong>S/ {{ number_format($total,2) }}</strong></td></tr>
                </table>
            </div>
        </body>
        </html>
