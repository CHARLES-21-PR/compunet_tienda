<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Create a demo SUNAT invoice/boleta for the given order_id.
     * This generates a simulated SUNAT payload and stores it in `invoices.data`.
     */
    public function createInvoice(array $orderData): array
    {
        $orderId = $orderData['order_id'] ?? null;
        if (! $orderId) {
            return ['error' => 'order_id_required'];
        }

        try {
            $order = \App\Models\Order::with('items')->find($orderId);
            if (! $order) {
                return ['error' => 'order_not_found'];
            }

            // Determine document type: factura (01) if customer RUC present, otherwise boleta (03)
            $customer = $order->user ? $order->user : null;
            $ship = is_array($order->shipping_address) ? $order->shipping_address : ($order->shipping_address ? json_decode($order->shipping_address, true) : []);
            // Preferir RUC si existe en shipping_address y tiene 11 dígitos (valor de RUC peruano)
            $customerRuc = $ship['ruc'] ?? ($customer->ruc ?? null);
            if (! empty($customerRuc) && ctype_digit((string) $customerRuc) && strlen((string) $customerRuc) === 11) {
                $docType = '01'; // factura
            } else {
                $docType = '03'; // boleta
            }

            $seriesPrefix = $docType === '01' ? 'F001' : 'B001';

            // compute next sequential number for this series
            $last = \Illuminate\Support\Facades\DB::table('invoices')->where('invoice_number', 'like', $seriesPrefix.'-%')->orderBy('id', 'desc')->first();
            $nextNum = 1;
            if ($last && preg_match('/-(\d+)$/', $last->invoice_number, $m)) {
                $nextNum = intval($m[1]) + 1;
            }
            $numberFormatted = str_pad($nextNum, 8, '0', STR_PAD_LEFT);
            $invoiceNumber = $seriesPrefix.'-'.$numberFormatted;

            // Build items array and totals
            $items = [];
            $subtotal = 0.0;
            foreach ($order->items as $it) {
                $lineTotal = floatval($it->price) * intval($it->quantity);
                $items[] = [
                    'product_id' => $it->product_id,
                    'description' => $it->name,
                    'quantity' => intval($it->quantity),
                    'unit_price' => floatval($it->price),
                    'line_total' => round($lineTotal, 2),
                ];
                $subtotal += $lineTotal;
            }
            // Enriquecer items con la categoría cuando exista product_id
            try {
                $productIds = [];
                foreach ($items as $it) {
                    if (! empty($it['product_id'])) {
                        $productIds[] = $it['product_id'];
                    }
                }
                if (! empty($productIds)) {
                    $products = \App\Models\Product::with('category')->whereIn('id', array_values(array_unique($productIds)))->get()->keyBy('id');
                    foreach ($items as &$itRef) {
                        $itRef['categoria'] = null;
                        if (! empty($itRef['product_id']) && isset($products[$itRef['product_id']])) {
                            $prod = $products[$itRef['product_id']];
                            if ($prod && isset($prod->category) && ! empty($prod->category->name)) {
                                $itRef['categoria'] = (string) $prod->category->name;
                            }
                        }
                    }
                    unset($itRef);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('InvoiceService: no se pudo enriquecer categoría de items: '.$e->getMessage());
            }

            $subtotal = round($subtotal, 2);
            $igv = round($subtotal * 0.18, 2);
            $total = round($subtotal + $igv, 2);

            // issuer data: read from env when available to use real RUC/name/address
            $issuer = [
                'ruc' => env('ISSUER_RUC', '20400000000'),
                'name' => env('ISSUER_NAME', 'EMPRESA DEMO S.A.C.'),
                'address' => env('ISSUER_ADDRESS', 'Av. Demo 123, Lima'),
            ];
            if ($issuer['ruc'] === '20400000000') {
                Log::warning('InvoiceService: usando RUC demo 20400000000. Configure ISSUER_RUC/ISSUER_NAME/ISSUER_ADDRESS en .env para usar el emisor real.');
            }

            // customer info
            $customerName = $ship['name'] ?? ($customer->name ?? 'Cliente');
            $customerDoc = $customerRuc ?? ($ship['dni'] ?? null);

            // Validar formato del documento fiscal antes de enviar al proveedor
            if ($docType === '01') { // factura -> RUC required (11 digits)
                if (empty($customerDoc) || ! ctype_digit((string) $customerDoc) || strlen((string) $customerDoc) !== 11) {
                    return [
                        'success' => false,
                        'error' => 'invalid_ruc',
                        'message' => 'RUC inválido o ausente. Para emitir factura el RUC debe tener 11 dígitos numéricos y estar activo en SUNAT.',
                    ];
                }
            } else { // boleta -> DNI required (8 digits)
                if (empty($customerDoc) || ! ctype_digit((string) $customerDoc) || strlen((string) $customerDoc) !== 8) {
                    return [
                        'success' => false,
                        'error' => 'invalid_dni',
                        'message' => 'DNI inválido o ausente. Para emitir boleta el DNI debe tener 8 dígitos numéricos.',
                    ];
                }
            }

            $sunatPayload = [
                'type' => $docType,
                'series' => $seriesPrefix,
                'number' => $numberFormatted,
                'invoice_number' => $invoiceNumber,
                'issue_date' => now()->format('Y-m-d'),
                'issuer' => $issuer,
                'customer' => [
                    'name' => $customerName,
                    'doc' => $customerDoc,
                ],
                'items' => $items,
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'order_id' => $order->id,
            ];

            // Determine currency code expected by the provider: 1=S/, 2=US$, 3=€, 4=£
            $orderCurrency = null;
            if (! empty($order->currency)) {
                $orderCurrency = $order->currency;
            } elseif (! empty($ship['currency'])) {
                $orderCurrency = $ship['currency'];
            }
            $currency = strtoupper(trim((string) ($orderCurrency ?? 'PEN')));
            $currencyCode = 1; // default to PEN
            if (in_array($currency, ['USD', 'US$', '$', 'DOLAR', 'DÓLAR', 'DOLLAR'])) {
                $currencyCode = 2;
            } elseif (in_array($currency, ['EUR', '€', 'EURO'])) {
                $currencyCode = 3;
            } elseif (in_array($currency, ['GBP', '£', 'LIBRA', 'POUND'])) {
                $currencyCode = 4;
            }

            // Build payload compatible with external provider or Greenter
            $providerPayload = [
                'operacion' => 'generar_comprobante',
                'tipo_de_comprobante' => $docType, // '01' or '03'
                'serie' => $seriesPrefix,
                'numero' => ltrim($numberFormatted, '0'),
                'cliente_tipo_de_documento' => (! empty($customerDoc) && strlen($customerDoc) >= 11) ? '6' : '1',
                'cliente_numero_de_documento' => $customerDoc ?? '',
                'cliente_denominacion' => $customerName,
                'cliente_direccion' => $ship['address'] ?? '',
                'fecha_de_emision' => now()->format('Y-m-d'),
                'moneda' => $currencyCode,
                'total_gravada' => number_format($subtotal, 2, '.', ''),
                'total_igv' => number_format($igv, 2, '.', ''),
                'total' => number_format($total, 2, '.', ''),
                'items' => [],
            ];

            // If payment info was provided, sanitize and add a short observation only
            if (! empty($orderData['payment']) && is_array($orderData['payment'])) {
                $p = $orderData['payment'];
                $method = isset($p['method']) ? strtolower(trim((string) $p['method'])) : 'unknown';
                $allowed = ['card', 'yape', 'cash', 'transfer', 'paypal', 'simulated', 'unknown'];
                if (! in_array($method, $allowed)) {
                    // attempt to normalize common values
                    if (stripos($method, 'card') !== false || stripos($method, 'tarjeta') !== false) {
                        $method = 'card';
                    } elseif (stripos($method, 'yape') !== false) {
                        $method = 'yape';
                    } elseif (stripos($method, 'paypal') !== false) {
                        $method = 'paypal';
                    } elseif (stripos($method, 'cash') !== false || stripos($method, 'efectivo') !== false) {
                        $method = 'cash';
                    } else {
                        $method = 'unknown';
                    }
                }

                $txn = isset($p['transaction_id']) ? preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $p['transaction_id']) : null;
                if ($txn && strlen($txn) > 64) {
                    $txn = substr($txn, 0, 64);
                }

                $obs = 'Pago: '.strtoupper($method);
                if ($txn) {
                    $obs .= ' Txn:'.$txn;
                }
                // The external provider may accept an observations field; include a short one to avoid sending raw gateway data
                $providerPayload['observaciones'] = $obs;
            }

            foreach ($items as $it) {
                $valor_unitario = number_format($it['unit_price'], 2, '.', '');
                $precio_unitario = number_format(round($it['unit_price'] * 1.18, 2), 2, '.', '');
                $subtotal_line = number_format($it['line_total'], 2, '.', '');
                $igv_line = number_format(round($it['line_total'] * 0.18, 2), 2, '.', '');
                $total_line = number_format(round($it['line_total'] * 1.18, 2), 2, '.', '');
                $providerItem = [
                    'unidad_de_medida' => 'NIU',
                    'codigo' => $it['product_id'] ?? '',
                    'descripcion' => $it['description'],
                    'cantidad' => $it['quantity'],
                    'valor_unitario' => $valor_unitario,
                    'precio_unitario' => $precio_unitario,
                    'subtotal' => $subtotal_line,
                    'tipo_de_igv' => '1',
                    'igv' => $igv_line,
                    'total' => $total_line,
                ];
                // Copiar categoria cuando esté disponible para conservarla también en la sección 'provider'
                if (! empty($it['categoria'])) {
                    $providerItem['categoria'] = $it['categoria'];
                }
                $providerPayload['items'][] = $providerItem;
            }

            // Delegate to GreenterService when enabled (recommended). Otherwise fall back to the configured provider.
            $sunatResponse = ['error' => 'not_sent'];
            if (env('USE_GREENTER', true)) {
                $greenterService = app(\App\Services\GreenterService::class);
                $greRes = $greenterService->createInvoice(['order' => $order, 'payload' => $providerPayload, 'invoice_number' => $invoiceNumber]);
                if (! empty($greRes['success'])) {
                    $sunatResponse = $greRes['response'] ?? ['status' => 'success', 'message' => 'generated_local'];
                    // Attach saved_files and file_path so later logic can pick them up
                    $savedFiles = $greRes['saved_files'] ?? [];
                    if (! empty($greRes['file_path'])) {
                        $filePath = $greRes['file_path'];
                    }
                    // If Greenter didn't produce a PDF, attempt to generate one via Dompdf here
                    $hasPdf = false;
                    foreach ($savedFiles as $sf) {
                        if (is_string($sf) && str_ends_with(strtolower($sf), '.pdf')) {
                            $hasPdf = true;
                            break;
                        }
                    }
                    if (! $hasPdf) {
                        try {
                            $html = view('invoices.sunat', ['order' => $order, 'payload' => $providerPayload, 'invoiceNumber' => $invoiceNumber])->render();
                            $dompdf = new \Dompdf\Dompdf;
                            $dompdf->loadHtml($html);
                            $dompdf->setPaper('A4', 'portrait');
                            $dompdf->render();
                            $pdfFilename = $invoiceNumber.'.pdf';
                            $pdfRel = 'invoices/'.$pdfFilename;
                            Storage::put($pdfRel, $dompdf->output());
                            $savedFiles[] = $pdfRel;
                            $filePath = $pdfRel;
                            if (is_array($sunatResponse)) {
                                $sunatResponse['enlace_del_pdf'] = url('/storage/'.$pdfRel);
                            }
                        } catch (\Throwable $e) {
                            Log::warning('InvoiceService: Dompdf fallback generation failed: '.$e->getMessage());
                        }
                    }
                } else {
                    $sunatResponse = ['status' => 'error', 'message' => $greRes['message'] ?? 'greenter_failed', 'debug' => $greRes];
                }
            } else {
                // Try to send to configured provider if Greenter is disabled
                $providerUrl = env('PROVIDER_URL', env('NUBEFACT_URL', 'https://api.nubefact.com/api/v1/c1466e83-ee39-418d-90cb-ca007c256b82'));
                $providerToken = env('PROVIDER_TOKEN', env('NUBEFACT_TOKEN', ''));

                try {
                    $res = Http::withHeaders([
                        'Authorization' => 'Token token="'.$providerToken.'"',
                        'Content-Type' => 'application/json',
                    ])->timeout(20)->post($providerUrl, $providerPayload);

                    if ($res->successful()) {
                        $sunatResponse = $res->json();
                    } else {
                        $sunatResponse = ['status' => 'error', 'http_code' => $res->status(), 'body' => $res->body()];
                    }
                } catch (\Throwable $e) {
                    Log::error('Provider request failed: '.$e->getMessage());
                    $sunatResponse = ['status' => 'error', 'message' => $e->getMessage()];
                }
            }

            // persist invoice with provider response (initially without file_path)
            $dataToStore = ['payload' => $sunatPayload, 'provider' => $providerPayload, 'response' => $sunatResponse];
            $id = \Illuminate\Support\Facades\DB::table('invoices')->insertGetId([
                'order_id' => $order->id,
                'invoice_number' => $invoiceNumber,
                'data' => json_encode($dataToStore),
                'file_path' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! isset($filePath)) {
                $filePath = null;
            }
            if (! isset($savedFiles)) {
                $savedFiles = [];
            }
            // Detectar respuestas del proveedor/SUNAT que indiquen estado 'BAJA' o 'NO HABIDO'
            $responseText = '';
            if (is_array($sunatResponse)) {
                if (! empty($sunatResponse['body'])) {
                    $responseText = (string) $sunatResponse['body'];
                } else {
                    $responseText = json_encode($sunatResponse);
                }
            } else {
                $responseText = (string) $sunatResponse;
            }

            $problematicPatterns = [
                'BAJA PROV', 'NO HABIDO', 'BAJA', 'NO HALLADO', 'NO EXISTE',
                'NO PUEDE GENERAR', 'SIN DOCUMENTO A MODIFICAR', 'Tipo de nota de cr', 'Tipo de nota de crédito',
                'Serie No puedes emitir comprobantes',
            ];
            $isProblematic = false;
            foreach ($problematicPatterns as $pat) {
                if (! empty($responseText) && stripos($responseText, $pat) !== false) {
                    $isProblematic = true;
                    break;
                }
            }

            if ($isProblematic) {
                // Actualizar la fila de invoice con la respuesta y devolver error legible
                $updatedData = $dataToStore;
                $updatedData['saved_files'] = $savedFiles;
                $updatedData['issue_detected'] = true;
                $updatedData['issue_message'] = $responseText;
                \Illuminate\Support\Facades\DB::table('invoices')->where('id', $id)->update(['data' => json_encode($updatedData), 'updated_at' => now()]);

                return [
                    'success' => false,
                    'error' => 'sunat_rejection',
                    'message' => 'Proveedor/SUNAT rechazó la emisión: '.$responseText,
                    'invoice_id' => $id,
                    'provider_response' => $sunatResponse,
                ];
            }
            // If provider returned links or base64 payloads, try to download/save them
            try {
                // Common link fields
                $linkFields = ['enlace', 'enlace_del_pdf', 'enlace_del_zip', 'enlace_del_xml', 'enlace_pdf', 'enlace_zip'];
                foreach ($linkFields as $lf) {
                    if (is_array($sunatResponse) && ! empty($sunatResponse[$lf])) {
                        $url = $sunatResponse[$lf];
                        try {
                            $resp = Http::timeout(20)->get($url);
                            if ($resp->successful()) {
                                // try to infer extension from headers or url
                                $ext = 'bin';
                                $contentType = $resp->header('Content-Type');
                                if (strpos($contentType, 'pdf') !== false) {
                                    $ext = 'pdf';
                                } elseif (strpos($contentType, 'zip') !== false) {
                                    $ext = 'zip';
                                } elseif (strpos($contentType, 'xml') !== false) {
                                    $ext = 'xml';
                                } else {
                                    $uParts = pathinfo(parse_url($url, PHP_URL_PATH));
                                    if (! empty($uParts['extension'])) {
                                        $ext = $uParts['extension'];
                                    }
                                }
                                $filename = $invoiceNumber.'-'.$lf.'.'.$ext;
                                $relPath = 'invoices/'.$filename;
                                Storage::put($relPath, $resp->body());
                                $savedFiles[] = $relPath;
                                if (! $filePath) {
                                    $filePath = $relPath;
                                }
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Download link failed ('.$lf.'): '.$e->getMessage());
                        }
                    }
                }

                // Base64 fields (zip/pdf/xml/cdr)
                $base64Fields = [
                    'pdf_zip_base64' => 'zip',
                    'xml_zip_base64' => 'zip',
                    'cdr_zip_base64' => 'zip',
                    'pdf_base64' => 'pdf',
                    'xml_base64' => 'xml',
                ];
                foreach ($base64Fields as $field => $ext) {
                    if (is_array($sunatResponse) && ! empty($sunatResponse[$field])) {
                        try {
                            $b64 = $sunatResponse[$field];
                            // sometimes providers return large base64 strings with data:...prefix
                            if (strpos($b64, 'base64,') !== false) {
                                $b64 = substr($b64, strpos($b64, 'base64,') + 7);
                            }
                            $content = base64_decode($b64);
                            if ($content !== false) {
                                $filename = $invoiceNumber.'-'.$field.'.'.$ext;
                                $relPath = 'invoices/'.$filename;
                                Storage::put($relPath, $content);
                                $savedFiles[] = $relPath;
                                if (! $filePath) {
                                    $filePath = $relPath;
                                }
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Failed to save base64 file ('.$field.'): '.$e->getMessage());
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to process invoice files: '.$e->getMessage());
            }

            // Update invoice row with file_path and saved_files inside data
            $updatedData = $dataToStore;
            $updatedData['saved_files'] = $savedFiles;
            \Illuminate\Support\Facades\DB::table('invoices')->where('id', $id)->update(['file_path' => $filePath, 'data' => json_encode($updatedData), 'updated_at' => now()]);

            return [
                'success' => true,
                'invoice_number' => $invoiceNumber,
                'id' => $id,
                'provider_response' => $sunatResponse,
                'payload' => $sunatPayload,
                'file_path' => $filePath,
                'saved_files' => $savedFiles,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
