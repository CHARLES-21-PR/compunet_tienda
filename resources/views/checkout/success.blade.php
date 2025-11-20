<x-app-layout>
    @section('content')
    <div class="container py-5">
        <div class="card shadow-lg border-0 overflow-hidden">
            <div class="row g-0">
                <div class="col-md-8" style="background: linear-gradient(90deg, rgba(99,102,241,0.08), rgba(99,102,241,0.03));">
                    <div class="p-4 p-md-5">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:64px;height:64px;background:linear-gradient(135deg,#10b981,#06b6d4);color:white;font-size:28px;">
                                ✓
                            </div>
                            <div>
                                <h2 class="mb-1">Compra exitosa</h2>
                                <p class="mb-0 text-muted">Gracias por tu compra. Hemos procesado tu orden correctamente.</p>
                                <p class="mt-3 mb-0 small text-muted">Orden <strong>#{{ $order->id }}</strong></p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <div class="mb-3">
                                        @php
                                            $orderTotal = floatval($order->total);
                                            $orderSubtotal = round($orderTotal / 1.18, 2);
                                            $orderIgv = round($orderTotal - $orderSubtotal, 2);
                                        @endphp
                                        <div class="small text-muted">Subtotal</div>
                                        <div class="fw-semibold">S/.{{ number_format($orderSubtotal,2) }}</div>
                                        <div class="small text-muted mt-2">IGV (18%)</div>
                                        <div class="fw-semibold">S/.{{ number_format($orderIgv,2) }}</div>
                                        <div class="small text-muted mt-2">Total</div>
                                        <div class="h4 fw-bold">S/.{{ number_format($orderTotal,2) }}</div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <div class="mb-3">
                                        @php
                                            $statusKey = strtolower($order->status ?? 'pending');
                                            $statusDefs = config('orders.statuses', []);
                                            $stDef = $statusDefs[$statusKey] ?? null;
                                            $statusLabel = $stDef['label'] ?? ucfirst($statusKey);
                                            // Prefer explicit Bootstrap badge classes for good contrast. If config provides custom classes
                                            // we will try to use them, otherwise fall back to sensible Bootstrap mappings.
                                            $bootstrapMap = [
                                                'paid' => 'badge bg-success text-white',
                                                'pagado' => 'badge bg-success text-white',
                                                'delivered' => 'badge bg-primary text-white',
                                                'delivered' => 'badge bg-primary text-white',
                                                'cancelled' => 'badge bg-warning text-dark',
                                                'failed' => 'badge bg-danger text-white',
                                                'pending' => 'badge bg-secondary text-white',
                                            ];
                                            $badgeClass = $bootstrapMap[$statusKey] ?? null;
                                            if (! $badgeClass && $stDef) {
                                                // if config defines bg/text classes, use them but ensure 'badge' present
                                                $bg = $stDef['bg'] ?? '';
                                                $text = $stDef['text'] ?? '';
                                                $badgeClass = trim(('badge ' . $bg . ' ' . $text));
                                            }
                                            if (! $badgeClass) $badgeClass = 'badge bg-secondary text-white';
                                        @endphp
                                        <div class="small text-muted">Estado del pedido</div>
                                        <div class="mt-2">
                                            <span class="{{ $badgeClass }} px-3 py-2 fs-6">{{ $statusLabel }}</span>
                                        </div>

                                        @if($payment)
                                            <div class="mt-3">
                                                <div class="small text-muted">Pago</div>
                                                <div class="fw-medium">{{ $payment->transaction_id ?? $payment->id }} <span class="small text-capitalize text-muted">— {{ $payment->status }}</span></div>
                                                @php
                                                    // Try to extract uploaded receipt for Yape
                                                    $paymentMeta = null;
                                                    try { $paymentMeta = is_string($payment->metadata) ? json_decode($payment->metadata, true) : $payment->metadata; } catch (\Throwable $e) { $paymentMeta = null; }
                                                    $receiptPath = $paymentMeta['receipt_path'] ?? null;
                                                @endphp
                                                @if(!empty($receiptPath))
                                                    @php
                                                        if (preg_match('#^https?://#i', $receiptPath)) {
                                                            $receiptUrl = $receiptPath;
                                                        } else {
                                                            $receiptUrl = asset('storage/' . ltrim($receiptPath, '/'));
                                                        }
                                                    @endphp
                                                    <div class="mt-2">
                                                        <a href="{{ $receiptUrl }}" target="_blank" class="btn btn-sm btn-outline-success">Ver comprobante (voucher)</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($invoice)
                                            @php $invFile = $invoice->file_path ?? null; $invBn = $invFile ? basename($invFile) : null; @endphp
                                            <div class="mt-3">
                                                <div class="small text-muted">Factura</div>
                                                <div class="fw-medium">
                                                    @if($invBn)
                                                        @php $invCode = pathinfo($invBn, PATHINFO_FILENAME); @endphp
                                                        <a href="{{ route('client.orders.invoice.download', $order->id) }}?file={{ urlencode($invBn) }}" class="text-decoration-none">{{ $invoice->invoice_number ?? $invCode }}</a>
                                                    @else
                                                        {{ $invoice->invoice_number ?? $invoice->id }}
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('categories.index') }}" class="btn btn-primary me-2">Seguir comprando</a>
                            <a href="/" class="btn btn-outline-secondary">Inicio</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 bg-white">
                    <div class="p-4">
                        <h6 class="text-muted">Resumen</h6>
                        <ul class="list-unstyled mt-3">
                            @foreach($items as $it)
                                <li class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="text-truncate" style="max-width:150px;">{{ $it->name }}</div>
                                    <div class="text-muted">{{ $it->quantity }}× S/.{{ number_format($it->price,2) }}</div>
                                </li>
                            @endforeach
                        </ul>
                        <hr>
                        <div class="d-flex justify-content-between"><div class="text-muted">Subtotal</div><div>S/.{{ number_format($orderSubtotal,2) }}</div></div>
                        <div class="d-flex justify-content-between"><div class="text-muted">IGV</div><div>S/.{{ number_format($orderIgv,2) }}</div></div>
                        <div class="d-flex justify-content-between mt-2"><div class="fw-semibold">Total</div><div class="fw-bold">S/.{{ number_format($orderTotal,2) }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal: Compra exitosa -->
    @if(session('just_paid') || request()->query('just_paid'))
    <div class="modal fade" id="purchaseSuccessModal" tabindex="-1" aria-labelledby="purchaseSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseSuccessModalLabel">Compra exitosa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p>Gracias por tu compra. Tu orden <strong>#{{ $order->id }}</strong> se ha procesado correctamente.</p>
                    @if($invoice)
                        <p>Factura: <strong>{{ $invoice->invoice_number ?? $invoice->id }}</strong></p>
                    @endif
                </div>
                <div class="modal-footer">
                    @if($invoice && !empty($invoice->file_path))
                        @php $bn = basename($invoice->file_path); @endphp
                        <a href="{{ route('client.orders.invoice.download', $order->id) }}?file={{ urlencode($bn) }}" class="btn btn-outline-primary" title="Descargar comprobante">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 0h5.5L14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zM9.5 1v3a1 1 0 0 0 1 1h3l-4-4z"/></svg>
                        </a>
                    @endif
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            try {
                var modalEl = document.getElementById('purchaseSuccessModal');
                if (modalEl) {
                    var modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            } catch (err) {
                console.warn('Bootstrap modal not available to show purchase success modal');
            }
        })();
    </script>
    @endif
    @endsection
</x-app-layout>