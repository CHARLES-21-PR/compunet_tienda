<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderSettingsController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['invoice', 'payments', 'user'])->orderBy('created_at', 'desc');

        // filter by status if provided. Accept Spanish and English keys (map aliases)
        $status = $request->query('status');
        if ($status) {
            $s = strtolower($status);
            $aliases = [
                'paid' => 'pagado', 'delivered' => 'entregado', 'cancelled' => 'cancelado', 'failed' => 'fallido', 'pending' => 'pendiente',
                'pagado' => 'paid', 'entregado' => 'delivered', 'cancelado' => 'cancelled', 'fallido' => 'failed', 'pendiente' => 'pending',
            ];
            $candidates = [$s];
            if (isset($aliases[$s])) {
                $candidates[] = $aliases[$s];
            }
            // query DB for any of the candidate status values
            $query->whereIn('status', $candidates);
        }

        // filter by payment method (supports orders.payment_method column or payments relation)
        $paymentMethod = $request->query('payment_method');
        if ($paymentMethod) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'payment_method')) {
                $query->where(function ($q) use ($paymentMethod) {
                    $q->where('payment_method', $paymentMethod)
                        ->orWhereHas('payments', function ($qp) use ($paymentMethod) {
                            $qp->where('method', $paymentMethod);
                        });
                });
            } else {
                $query->whereHas('payments', function ($qp) use ($paymentMethod) {
                    $qp->where('method', $paymentMethod);
                });
            }
        }

        $orders = $query->paginate(10)->withQueryString();
        // pass available statuses for the filter UI (from DB if available, fallback to config)
        $availableStatuses = [];
        try {
            $statusRows = \App\Models\OrderStatus::orderBy('id')->get();
            if ($statusRows->isNotEmpty()) {
                foreach ($statusRows as $r) {
                    $availableStatuses[$r->key] = $r->label;
                }
            }
        } catch (\Throwable $e) {
            // fallback to config
        }
        if (empty($availableStatuses)) {
            // fallback default statuses (usamos claves en español)
            $defaults = ['pagado' => 'Pagado', 'entregado' => 'Entregado', 'cancelado' => 'Cancelado', 'fallido' => 'Fallido', 'pendiente' => 'Pendiente'];
            foreach ($defaults as $k => $v) {
                $availableStatuses[$k] = $v;
            }
        }
        // gather available payment methods for the filter UI
        $availablePaymentMethods = [];
        try {
            // Prefer methods from payments table
            if (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                $availablePaymentMethods = \App\Models\Payment::select('method')->distinct()->whereNotNull('method')->orderBy('method')->pluck('method')->toArray();
            }
        } catch (\Throwable $e) {
            $availablePaymentMethods = [];
        }

        // fallback: if orders table stores method on the model
        if (empty($availablePaymentMethods)) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('orders') && \Illuminate\Support\Facades\Schema::hasColumn('orders', 'payment_method')) {
                    $availablePaymentMethods = \App\Models\Order::select('payment_method')->distinct()->whereNotNull('payment_method')->orderBy('payment_method')->pluck('payment_method')->toArray();
                }
            } catch (\Throwable $e) {
                $availablePaymentMethods = [];
            }
        }

        return view('admin.orders.index', compact('orders', 'availableStatuses', 'availablePaymentMethods'));
    }

    public function show(\Illuminate\Http\Request $request, $id)
    {
        $order = Order::with(['items', 'payments', 'user'])->find($id);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }
        $invoice = $order->invoice()->orderBy('id', 'desc')->first();
        // If the request is AJAX, return only the partial fragment (no layout) for modal display
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return view('admin.orders.partials.show', compact('order', 'invoice'));
        }

        return view('admin.orders.show', compact('order', 'invoice'));
    }

    public function edit($id)
    {
        $order = Order::with('user')->find($id);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }
        return view('admin.orders.edit', compact('order'));
    }

    /**
     * Actualizar el estado del pedido (solo admin)
     */
    public function update(\Illuminate\Http\Request $request, $id)
    {
        $order = Order::find($id);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }

        // Solo permitir los estados solicitados: por defecto 'paid' y los estados admin-editables
        // allowed states come from DB (order_statuses) or config fallback
        $allowed = [];
        try {
            $allowed = \App\Models\OrderStatus::pluck('key')->toArray();
        } catch (\Throwable $e) {
            // fallback to spanish keys
            $allowed = ['pagado', 'entregado', 'cancelado', 'fallido', 'pendiente'];
        }
        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', $allowed)],
            'total' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string'],
            'created_at' => ['nullable', 'date'],
        ]);

        $order->status = $data['status'];
        
        if (isset($data['total'])) {
            $order->total = $data['total'];
        }

        if (isset($data['payment_method']) && \Illuminate\Support\Facades\Schema::hasColumn('orders', 'payment_method')) {
            $order->payment_method = $data['payment_method'];
        }

        if (isset($data['created_at'])) {
            $order->created_at = $data['created_at'];
        }

        $order->save();

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Pedido actualizado correctamente');
    }

    /**
     * Eliminar un pedido (solo admin)
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }

        try {
            $order->delete();

            return redirect()->route('admin.orders.index')->with('success', 'Pedido eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')->with('error', 'No se pudo eliminar el pedido');
        }
    }

    /**
     * Generar (o re-generar) factura/boleta para un pedido usando InvoiceService
     */
    public function generateInvoice(\Illuminate\Http\Request $request, $id)
    {
        $order = Order::find($id);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }

        // Only allow invoice generation for orders that are paid
        if (($order->status ?? '') !== 'pagado') {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Solo se puede generar factura para pedidos con estado "pagado".');
        }

        $isYape = false;
        if (strtolower($order->payment_method ?? '') === 'yape') {
            $isYape = true;
        } elseif ($order->payments && $order->payments->isNotEmpty()) {
            foreach($order->payments as $p) {
                if (strtolower($p->method ?? '') === 'yape') {
                    $isYape = true;
                    break;
                }
            }
        }

        if ($isYape) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'No se puede generar comprobante con el método de pago Yape.');
        }

        try {
            $invoiceService = app(\App\Services\InvoiceService::class);
            $res = $invoiceService->createInvoice(['order_id' => $order->id]);
            if (! empty($res['success'])) {
                // If the request intends to download immediately (non-AJAX fallback), redirect to export endpoint
                if ($request->input('download')) {
                    return redirect()->route('admin.orders.export_xml', $order->id);
                }

                return redirect()->route('admin.orders.show', $order->id)->with('success', 'Factura/Boleta generada: '.($res['invoice_number'] ?? 'OK'));
            }

            $err = $res['error'] ?? ($res['provider_response']['body'] ?? json_encode($res));

            return redirect()->route('admin.orders.show', $order->id)->with('error', 'No se pudo generar la factura: '.substr($err, 0, 300));
        } catch (\Throwable $e) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Error al generar factura: '.$e->getMessage());
        }
    }

    /**
     * Descargar archivo de comprobante asociado a una factura (PDF/ZIP).
     */
    public function downloadInvoice(Request $request, $invoiceId)
    {
        $invoice = \App\Models\Invoice::find($invoiceId);
        if (! $invoice) {
            return back()->with('error', 'Factura no encontrada');
        }

        // If a specific file param is provided, validate it's in saved_files and serve it
        $file = $request->query('file');
        $data = json_decode($invoice->data, true) ?: [];
        $saved = $data['saved_files'] ?? [];
        if ($file) {
            // allow passing basename only
            $basename = basename($file);
            foreach ($saved as $p) {
                if (basename($p) === $basename && Storage::exists($p)) {
                    return Storage::download($p, $basename);
                }
            }

            return back()->with('error', 'Archivo no encontrado para esta factura');
        }

        if (empty($invoice->file_path) || ! Storage::exists($invoice->file_path)) {
            return back()->with('error', 'Archivo de factura no disponible para descarga');
        }

        return Storage::download($invoice->file_path, basename($invoice->file_path));
    }

    /**
     * Exportar/descargar el XML del comprobante asociado a un pedido.
     */
    public function exportInvoiceXml($orderId)
    {
        $order = Order::with('invoice')->find($orderId);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }

        $invoice = $order->invoice;
        if (! $invoice) {
            // Fallback: buscar en la tabla `invoices` una fila cuyo JSON `data` indique el order_id (por compatibilidad si order_id no fue guardado)
            try {
                $maybe = \Illuminate\Support\Facades\DB::table('invoices')->where('data', 'like', '%"order_id":'.intval($orderId).'%')->orderBy('id', 'desc')->first();
                if ($maybe) {
                    $invoice = \App\Models\Invoice::find($maybe->id);
                }
            } catch (\Throwable $e) {
                $invoice = null;
            }
        }
        if (! $invoice) {
            return redirect()->route('admin.orders.index')->with('error', 'No existe factura asociada a este pedido');
        }

        $data = json_decode($invoice->data, true) ?: [];

        $saved = $data['saved_files'] ?? [];

        // 1) Preferir PDF: buscar en saved_files una ruta con extension .pdf
        foreach ($saved as $path) {
            if (str_ends_with(strtolower($path), '.pdf')) {
                if (Storage::exists($path)) {
                    return Storage::download($path, basename($path));
                }
            }
        }

        // 2) Revisar file_path si apunta a PDF
        if (! empty($invoice->file_path) && str_ends_with(strtolower($invoice->file_path), '.pdf') && Storage::exists($invoice->file_path)) {
            return Storage::download($invoice->file_path, basename($invoice->file_path));
        }

        // 3) Revisar en response campos base64 o enlaces para PDF
        $response = $data['response'] ?? [];
        if (is_array($response)) {
            if (! empty($response['pdf_base64'])) {
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

            // enlaces (enlace_del_pdf, enlace_pdf, enlace)
            $linkFields = ['enlace_del_pdf', 'enlace_pdf', 'enlace'];
            foreach ($linkFields as $lf) {
                if (! empty($response[$lf])) {
                    $url = $response[$lf];
                    // If the link points to local storage (localhost, 127.0.0.1 or /storage/...), avoid HTTP and serve directly
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
                        // ignore and try HTTP as fallback
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
                        // ignore and continue to XML fallback
                    }
                }
            }
        }

        // Si no hay PDF, volver a la lógica original: buscar XML en saved_files
        foreach ($saved as $path) {
            if (str_ends_with(strtolower($path), '.xml')) {
                if (Storage::exists($path)) {
                    return Storage::download($path, basename($path));
                }
            }
        }

        // Buscar en response campos base64 de XML/ZIP
        if (is_array($response)) {
            if (! empty($response['xml_base64'])) {
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

            if (! empty($response['xml_zip_base64'])) {
                $b64 = $response['xml_zip_base64'];
                if (strpos($b64, 'base64,') !== false) {
                    $b64 = substr($b64, strpos($b64, 'base64,') + 7);
                }
                $content = base64_decode($b64);
                if ($content !== false) {
                    $name = ($invoice->invoice_number ?? 'comprobante').'.zip';

                    return response($content, 200, [
                        'Content-Type' => 'application/zip',
                        'Content-Disposition' => 'attachment; filename="'.$name.'"',
                    ]);
                }
            }
        }

        // Si el file_path apunta a zip, intentar servir el zip si existe
        if (! empty($invoice->file_path) && str_ends_with(strtolower($invoice->file_path), '.zip') && Storage::exists($invoice->file_path)) {
            return Storage::download($invoice->file_path, basename($invoice->file_path));
        }

        return redirect()->route('admin.orders.index')->with('error', 'Documento no disponible para este pedido');
    }

    /**
     * AJAX endpoint: generar factura y devolver JSON con resultados.
     */
    public function generateInvoiceAjax(Request $request, $id)
    {
        $order = Order::find($id);
        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Orden no encontrada'], 404);
        }

        // Only allow invoice generation for orders that are paid
        if (($order->status ?? '') !== 'pagado') {
            return response()->json(['success' => false, 'message' => 'Solo se puede generar factura para pedidos con estado "pagado".'], 422);
        }

        $isYape = false;
        if (strtolower($order->payment_method ?? '') === 'yape') {
            $isYape = true;
        } elseif ($order->payments && $order->payments->isNotEmpty()) {
            foreach($order->payments as $p) {
                if (strtolower($p->method ?? '') === 'yape') {
                    $isYape = true;
                    break;
                }
            }
        }

        if ($isYape) {
            return response()->json(['success' => false, 'message' => 'No se puede generar comprobante con el método de pago Yape.'], 400);
        }

        try {
            $invoiceService = app(\App\Services\InvoiceService::class);
            $res = $invoiceService->createInvoice(['order_id' => $order->id]);
            // If provider returned an error, surface it
            $providerResp = $res['provider_response'] ?? null;
            if (is_array($providerResp) && (isset($providerResp['status']) && $providerResp['status'] === 'error' || (isset($providerResp['http_code']) && $providerResp['http_code'] >= 400))) {
                // try to extract a readable message
                $msg = 'Error del proveedor';
                if (! empty($providerResp['body'])) {
                    $body = $providerResp['body'];
                    $decoded = json_decode($body, true);
                    if (is_array($decoded) && ! empty($decoded['errors'])) {
                        $msg = is_array($decoded['errors']) ? implode('; ', (array) $decoded['errors']) : $decoded['errors'];
                    } else {
                        $msg = $body;
                    }
                }

                return response()->json(['success' => false, 'message' => substr($msg, 0, 300)], 400);
            }

            if (! empty($res['success'])) {
                return response()->json(['success' => true, 'invoice_id' => $res['id'] ?? null, 'file_path' => $res['file_path'] ?? null, 'saved_files' => $res['saved_files'] ?? [], 'provider_response' => $res['provider_response'] ?? null]);
            }

            $err = $res['error'] ?? json_encode($res);

            return response()->json(['success' => false, 'message' => substr($err, 0, 300)], 400);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Fallback GET endpoint: genera la factura y redirige a la descarga (para navegadores sin JS o cuando el botón no funciona)
     */
    public function generateInvoiceDownload(Request $request, $id)
    {
        $order = Order::find($id);
        if (! $order) {
            return redirect()->route('admin.orders.index')->with('error', 'Orden no encontrada');
        }

        // Only allow invoice generation for orders that are paid
        if (($order->status ?? '') !== 'pagado') {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Solo se puede generar factura para pedidos con estado "pagado".');
        }

        $isYape = false;
        if (strtolower($order->payment_method ?? '') === 'yape') {
            $isYape = true;
        } elseif ($order->payments && $order->payments->isNotEmpty()) {
            foreach($order->payments as $p) {
                if (strtolower($p->method ?? '') === 'yape') {
                    $isYape = true;
                    break;
                }
            }
        }

        if ($isYape) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'No se puede generar comprobante con el método de pago Yape.');
        }

        try {
            $invoiceService = app(\App\Services\InvoiceService::class);
            $res = $invoiceService->createInvoice(['order_id' => $order->id]);

            // After creation, redirect to export endpoint which will try to serve PDF
            return redirect()->route('admin.orders.export_xml', $order->id);
        } catch (\Throwable $e) {
            return redirect()->route('admin.orders.show', $order->id)->with('error', 'Error al generar factura: '.$e->getMessage());
        }
    }
}
