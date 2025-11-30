<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClientOrderController extends Controller
{
    /**
     * Descargar el comprobante (PDF/XML/ZIP) asociado a un pedido, validando propietario.
     */
    public function downloadInvoice(Request $request, $orderId)
    {
        $order = Order::with('invoice')->find($orderId);
        if (! $order) {
            return back()->with('error', 'Pedido no encontrado');
        }
        // Only the owner can download
        $user = Auth::user();
        if (! $user || $user->id !== $order->user_id) {
            return back()->with('error', 'No autorizado');
        }

        $invoice = $order->invoice;
        if (! $invoice) {
            return back()->with('error', 'No existe factura asociada a este pedido');
        }

        $data = json_decode($invoice->data, true) ?: [];
        $saved = $data['saved_files'] ?? [];

        // If a specific file basename is requested, try to serve that exact saved_file
        $requestedFile = $request->query('file');
        if ($requestedFile) {
            $basenameReq = basename($requestedFile);
            foreach ($saved as $path) {
                if (basename($path) === $basenameReq && Storage::exists($path)) {
                    return Storage::download($path, $basenameReq);
                }
            }
            // if file not found in saved_files, also check invoice->file_path if basename matches
            if (! empty($invoice->file_path) && basename($invoice->file_path) === $basenameReq && Storage::exists($invoice->file_path)) {
                return Storage::download($invoice->file_path, $basenameReq);
            }
        }

        // 1) Preferir PDF en saved_files (first available)
        foreach ($saved as $path) {
            if (str_ends_with(strtolower($path), '.pdf') && Storage::exists($path)) {
                return Storage::download($path, basename($path));
            }
        }

        // 2) Revisar file_path
        if (! empty($invoice->file_path) && str_ends_with(strtolower($invoice->file_path), '.pdf') && Storage::exists($invoice->file_path)) {
            return Storage::download($invoice->file_path, basename($invoice->file_path));
        }

        // 3) Revisar response pdf_base64 o enlaces
        $response = $data['response'] ?? [];
        if (is_array($response) && ! empty($response['pdf_base64'])) {
            $b64 = $response['pdf_base64'];
            if (strpos($b64, 'base64,') !== false) {
                $b64 = substr($b64, strpos($b64, 'base64,') + 7);
            }
            $content = base64_decode($b64);
            if ($content !== false) {
                $name = ($invoice->invoice_number ?? 'comprobante').'.pdf';

                return response($content, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$name.'"',
                ]);
            }
        }

        // También intentar descargar desde links en response
        if (is_array($response)) {
            $linkFields = ['enlace_del_pdf', 'enlace_pdf', 'enlace'];
            foreach ($linkFields as $lf) {
                if (! empty($response[$lf])) {
                    $url = $response[$lf];
                    try {
                        $parsed = parse_url($url);
                        $path = $parsed['path'] ?? '';
                        if (strpos($path, '/storage/invoices/') !== false) {
                            $basename = basename($path);
                            $rel = 'invoices/'.$basename;
                            if (Storage::exists($rel)) {
                                return Storage::download($rel, $basename);
                            }
                        }
                    } catch (\Throwable $e) {
                        // continue
                    }

                    try {
                        $resp = \Illuminate\Support\Facades\Http::timeout(10)->get($url);
                        if ($resp->successful()) {
                            $contentType = $resp->header('Content-Type') ?? 'application/octet-stream';
                            $ext = 'bin';
                            if (strpos($contentType, 'pdf') !== false) {
                                $ext = 'pdf';
                            }
                            $name = ($invoice->invoice_number ?? 'comprobante').'.'.$ext;

                            return response($resp->body(), 200, [
                                'Content-Type' => $contentType,
                                'Content-Disposition' => 'attachment; filename="'.$name.'"',
                            ]);
                        }
                    } catch (\Throwable $e) {
                        // ignore and continue
                    }
                }
            }
        }

        // Si no hay PDF, intentar XML en saved_files
        foreach ($saved as $path) {
            if (str_ends_with(strtolower($path), '.xml') && Storage::exists($path)) {
                return Storage::download($path, basename($path));
            }
        }

        if (is_array($response) && ! empty($response['xml_base64'])) {
            $b64 = $response['xml_base64'];
            if (strpos($b64, 'base64,') !== false) {
                $b64 = substr($b64, strpos($b64, 'base64,') + 7);
            }
            $content = base64_decode($b64);
            if ($content !== false) {
                $name = ($invoice->invoice_number ?? 'comprobante').'.xml';

                return response($content, 200, [
                    'Content-Type' => 'application/xml',
                    'Content-Disposition' => 'attachment; filename="'.$name.'"',
                ]);
            }
        }

        // Última opción: regenerar el PDF desde la plantilla Blade `invoices.pdf` y devolverlo
        try {
            $payload = $data['payload'] ?? [];
            $invoiceNumber = $invoice->invoice_number ?? ($invoice->id ?? 'comprobante');
            $html = view('invoices.pdf', compact('order', 'payload', 'invoiceNumber'))->render();

            $dompdf = new Dompdf;
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $output = $dompdf->output();

            // Opcional: guardar en storage para reutilizar
            $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $invoiceNumber);
            $pdfRel = 'invoices/'.$safeName.'.pdf';
            try {
                Storage::put($pdfRel, $output);
            } catch (\Throwable $e) {
                Log::warning('ClientOrderController: no se pudo guardar PDF regenerado: '.$e->getMessage());
            }

            return response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.basename($pdfRel).'"',
            ]);
        } catch (\Throwable $e) {
            Log::error('ClientOrderController: regeneracion PDF fallida: '.$e->getMessage());

            return back()->with('error', 'No fue posible regenerar el comprobante.');
        }

    }
}
