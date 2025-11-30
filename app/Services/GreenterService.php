<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;

class GreenterService
{
    /**
     * Crear y (si es posible) enviar comprobante con Greenter.
     * Actualmente este scaffold genera un XML simple y devuelve xml_base64
     * Si se configura un .p12 y Greenter adecuadamente, aquí se integrará el envío real.
     */
    public function createInvoice(array $data): array
    {
        // Esperamos recibir 'order' (modelo) y 'invoice_number' o 'payload'
        $invoiceNumber = $data['invoice_number'] ?? ($data['payload']['invoice_number'] ?? ('INV-'.time()));
        $order = $data['order'] ?? null;
        $payload = $data['payload'] ?? [];

        // Check for required certificate config
        $p12 = env('GREENTER_P12_PATH');
        $p12pass = env('GREENTER_P12_PASS');

        // NOTE: local XML generation is disabled here. This scaffold will produce a PDF
        // and save it to storage. If you need signed XML for SUNAT, integrate signing
        // and sending separately.
        $response = ['status' => 'success', 'message' => 'Generated local PDF via GreenterService scaffold'];
        $savedFiles = [];
        $pdfRel = null;
        $filePath = null;

        // Try to generate PDF from a SUNAT-like Blade view.
        // The generator can be selected with env `PDF_GENERATOR`: 'wkhtmltopdf' (default) or 'dompdf'.
        $preferred = env('PDF_GENERATOR', 'wkhtmltopdf');
        try {
            $html = view('invoices.sunat', ['order' => $order, 'payload' => $payload, 'invoiceNumber' => $invoiceNumber])->render();

            // Ensure invoices directory exists
            $invoicesDir = storage_path('app/invoices');
            if (! file_exists($invoicesDir)) {
                @mkdir($invoicesDir, 0755, true);
            }

            // Provide two generation strategies and allow choosing via env
            if ($preferred === 'dompdf') {
                // Try Dompdf first
                try {
                    $dompdf = new \Dompdf\Dompdf;
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();
                    $pdfFilename = $invoiceNumber.'.pdf';
                    $pdfRel = 'invoices/'.$pdfFilename;
                    $content = $dompdf->output();
                    Storage::put($pdfRel, $content);
                    $savedFiles[] = $pdfRel;
                    $filePath = $pdfRel;
                    $response['enlace_del_pdf'] = url('/storage/'.$pdfRel);
                } catch (\Throwable $e) {
                    Log::warning('GreenterService: Dompdf generation failed: '.$e->getMessage());
                }

                // If Dompdf didn't produce, fallback to wkhtmltopdf
                if (empty($filePath)) {
                    try {
                        $pdf = new Pdf(['no-outline' => true]);
                        $pdf->addPage($html);
                        $pdfFilename = $invoiceNumber.'.pdf';
                        $pdfRel = 'invoices/'.$pdfFilename;
                        $fullPath = $invoicesDir.DIRECTORY_SEPARATOR.$pdfFilename;
                        if ($pdf->saveAs($fullPath)) {
                            $savedFiles[] = $pdfRel;
                            $filePath = $pdfRel;
                            $response['enlace_del_pdf'] = url('/storage/'.$pdfRel);
                        } else {
                            Log::warning('GreenterService: wkhtmltopdf failed: '.$pdf->getError());
                        }
                    } catch (\Throwable $e) {
                        Log::info('GreenterService: wkhtmltopdf not available or failed: '.$e->getMessage());
                    }
                }
            } else {
                // Default: try wkhtmltopdf first
                try {
                    $pdf = new Pdf(['no-outline' => true]);
                    $pdf->addPage($html);
                    $pdfFilename = $invoiceNumber.'.pdf';
                    $pdfRel = 'invoices/'.$pdfFilename;
                    $fullPath = $invoicesDir.DIRECTORY_SEPARATOR.$pdfFilename;
                    if ($pdf->saveAs($fullPath)) {
                        $savedFiles[] = $pdfRel;
                        $filePath = $pdfRel;
                        $response['enlace_del_pdf'] = url('/storage/'.$pdfRel);
                    } else {
                        Log::warning('GreenterService: wkhtmltopdf failed: '.$pdf->getError());
                    }
                } catch (\Throwable $e) {
                    Log::info('GreenterService: wkhtmltopdf not available or failed, falling back to Dompdf: '.$e->getMessage());
                }

                // If wkhtmltopdf didn't produce a PDF, try Dompdf (pure PHP)
                if (empty($filePath)) {
                    try {
                        $dompdf = new \Dompdf\Dompdf;
                        $dompdf->loadHtml($html);
                        $dompdf->setPaper('A4', 'portrait');
                        $dompdf->render();
                        $pdfFilename = $invoiceNumber.'.pdf';
                        $pdfRel = 'invoices/'.$pdfFilename;
                        $content = $dompdf->output();
                        // Save via Storage to respect filesystem
                        Storage::put($pdfRel, $content);
                        $savedFiles[] = $pdfRel;
                        $filePath = $pdfRel;
                        $response['enlace_del_pdf'] = url('/storage/'.$pdfRel);
                    } catch (\Throwable $e) {
                        Log::warning('GreenterService: Dompdf generation failed: '.$e->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GreenterService: PDF generation failed: '.$e->getMessage());
        }

        return [
            'success' => true,
            'response' => $response,
            'saved_files' => $savedFiles,
            'file_path' => $filePath,
        ];
    }

    protected function buildSimpleXml(array $payload, $order, $invoiceNumber)
    {
        $date = date('Y-m-d');
        $customerName = $payload['cliente_denominacion'] ?? ($order->user->name ?? 'Cliente');
        $customerDoc = $payload['cliente_numero_de_documento'] ?? ($order->shipping_address['dni'] ?? ($order->shipping_address['ruc'] ?? ''));

        $itemsXml = '';
        $items = $payload['items'] ?? [];
        if (empty($items) && $order) {
            foreach ($order->items as $it) {
                $itemsXml .= '<item><description>'.htmlspecialchars($it->name)."</description><quantity>{$it->quantity}</quantity><price>{$it->price}</price></item>";
            }
        } else {
            foreach ($items as $it) {
                $desc = htmlspecialchars($it['descripcion'] ?? ($it['description'] ?? ''));
                $qty = $it['cantidad'] ?? ($it['quantity'] ?? 1);
                $price = $it['valor_unitario'] ?? ($it['unit_price'] ?? 0);
                $itemsXml .= "<item><description>{$desc}</description><quantity>{$qty}</quantity><price>{$price}</price></item>";
            }
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<invoice>\n";
        $xml .= "  <number>{$invoiceNumber}</number>\n";
        $xml .= "  <date>{$date}</date>\n";
        $xml .= "  <customer>\n";
        $xml .= '    <name>'.htmlspecialchars($customerName)."</name>\n";
        $xml .= '    <doc>'.htmlspecialchars($customerDoc)."</doc>\n";
        $xml .= "  </customer>\n";
        $xml .= "  <items>\n".$itemsXml."\n  </items>\n";
        $xml .= "</invoice>\n";

        return $xml;
    }
}
