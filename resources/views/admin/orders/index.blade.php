<x-app-layout>

@section('content')

<div class="container-fluid">
    <div class="row g-0">
        <div class="col-12 col-md-3 px-0">
            @include('admin.partials.nav_cate')
        </div>
        <div id="settings-main" class="col-12 col-md-9 ps-md-1">
            <div class="bg-dark rounded-3 p-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h1 class="text-white mb-0">Pedidos</h1>
                    <div>
                        <form id="ordersFilterForm" method="GET" action="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-2">
                            <select id="ordersStatusSelect" name="status" class="form-select form-select-sm" style="height:40px; min-width: 180px;" onchange="document.getElementById('ordersFilterForm').submit();">
                                <option value="">Todos los estados</option>
                                @foreach($availableStatuses ?? [] as $k => $label)
                                    <option value="{{ $k }}" {{ request()->query('status') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>

                            <select id="ordersClientSelect" name="client_id" class="form-select form-select-sm" style="height:40px; min-width: 180px;" onchange="document.getElementById('ordersFilterForm').submit();">
                                <option value="">Todos los clientes</option>
                                @foreach($clients ?? [] as $client)
                                    <option value="{{ $client->id }}" {{ request()->query('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>

                            <select id="paymentMethodSelect" name="payment_method" class="form-select form-select-sm" style="height:40px; min-width: 160px;" onchange="document.getElementById('ordersFilterForm').submit();">
                                <option value="">Todos los métodos</option>
                                @foreach($availablePaymentMethods ?? [] as $m)
                                    <option value="{{ $m }}" {{ request()->query('payment_method') === $m ? 'selected' : '' }}>{{ ucfirst($m) }}</option>
                                @endforeach
                            </select>

                            @if(request()->query('status') || request()->query('payment_method') || request()->query('client_id'))
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
                            @endif
                        </form>
                    </div>
                </div>

                @if($orders->isEmpty())
                    <p class="text-white">No hay pedidos.</p>
                @else
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="settings-pagination-top w-100 d-flex justify-content-end">
                            {!! str_replace('<nav', '<nav style="background: rgba(33, 37, 41, 0.75); padding: .15rem .5rem; border-radius:10px; color: #fff; --bs-pagination-color: #fff; --bs-pagination-bg: transparent; --bs-pagination-border-color: rgba(255,255,255,0.06); --bs-pagination-hover-color: #fff; --bs-pagination-hover-bg: rgba(255,255,255,0.04); --bs-pagination-active-color: #0f172a; --bs-pagination-active-bg: #eef2ff;"', $orders->links('pagination::bootstrap-5')) !!}
                        </div>
                    </div>

                    <div class="table-responsive">
                    <table class="table table-dark rounded-3">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Metodo Pago</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $o)
                            <tr>
                                <td>{{ $o->id }}</td>
                                <td>
                                    @php $customer = $o->user ?? null; @endphp
                                    @if($customer)
                                        {{ $customer->name }}
                                    @else
                                        @if($o->user_id)
                                            Usuario #{{ $o->user_id }}
                                        @else
                                            Invitado
                                        @endif
                                    @endif
                                </td>
                                <td>{{ number_format($o->total,2) }}</td>
                                <td>
                                    @php
                                        $method = $o->payment_method ?? optional($o->payments->first())->method ?? null;
                                    @endphp
                                    {{ $method ? ucfirst($method) : 'N/A' }}
                                </td>
                                @php
                                    $statusMap = [
                                        'pagado' => ['label' => 'Pagado', 'class' => 'success'],
                                        'entregado' => ['label' => 'Entregado', 'class' => 'primary'],
                                        'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
                                        'fallido' => ['label' => 'Fallido', 'class' => 'danger'],
                                        'pendiente' => ['label' => 'Pendiente', 'class' => 'warning'],
                                    ];
                                    $st = strtolower($o->status ?? '');
                                    $aliases = [
                                        'paid' => 'pagado', 'delivered' => 'entregado', 'cancelled' => 'cancelado', 'failed' => 'fallido', 'pending' => 'pendiente'
                                    ];
                                    if (isset($aliases[$st])) $st = $aliases[$st];
                                    $badge = $statusMap[$st] ?? ['label' => ucfirst($st ?: 'Pendiente'), 'class' => 'secondary'];
                                @endphp
                                <td><span class="badge bg-{{ $badge['class'] }} @if($badge['class']==='warning' || $badge['class']==='info') text-dark @endif">{{ $badge['label'] }}</span></td>
                                <td>{{ $o->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $o->id) }}" class="btn btn-sm btn-secondary">Ver</a>
                                    <a href="{{ route('admin.orders.edit', $o->id) }}" class="btn btn-sm btn-primary ms-1">Editar</a>
                                    @php
                                        $xmlAvailable = false;
                                        if(!empty($o->invoice)){
                                            $data = json_decode($o->invoice->data, true) ?: [];
                                            if(!empty($data['saved_files'])){
                                                foreach($data['saved_files'] as $f){
                                                    if(str_ends_with(strtolower($f), '.xml')) { $xmlAvailable = true; break; }
                                                }
                                            }
                                            if(!$xmlAvailable && !empty($data['response'])){
                                                $resp = $data['response'];
                                                if(!empty($resp['xml_base64']) || !empty($resp['xml_zip_base64'])) $xmlAvailable = true;
                                            }
                                            if(!$xmlAvailable && !empty($o->invoice->file_path) && str_ends_with(strtolower($o->invoice->file_path), '.xml')) $xmlAvailable = true;
                                        }
                                    @endphp
                                    @php
                                        $pdfAvailable = false;
                                        if(!empty($o->invoice)){
                                            $d = json_decode($o->invoice->data, true) ?: [];
                                            $sf = $d['saved_files'] ?? [];
                                            foreach($sf as $f){ if(str_ends_with(strtolower($f), '.pdf')) { $pdfAvailable = true; break; } }
                                            if(!$pdfAvailable && !empty($o->invoice->file_path) && str_ends_with(strtolower($o->invoice->file_path), '.pdf')) $pdfAvailable = true;
                                            if(!$pdfAvailable && !empty($d['response']) && is_array($d['response'])){ $r = $d['response']; if(!empty($r['pdf_base64']) || !empty($r['enlace_del_pdf']) || !empty($r['enlace_pdf'])) $pdfAvailable = true; }
                                        }
                                        
                                        // Check if order is Yape to hide generation button
                                        $isYape = false;
                                        $pm = strtolower($o->payment_method ?? '');
                                        if($pm === 'yape') $isYape = true;
                                        if(!$isYape && $o->payments && $o->payments->isNotEmpty()){
                                            foreach($o->payments as $p){
                                                if(strtolower($p->method ?? '') === 'yape'){ $isYape = true; break; }
                                            }
                                        }
                                    @endphp
                                    @if($isYape)
                                        <button class="btn btn-sm btn-secondary ms-1" disabled title="No disponible para Yape">Sin comprobante</button>
                                    @elseif($pdfAvailable)
                                        <a href="{{ route('admin.orders.export_xml', $o->id) }}" class="btn btn-sm btn-outline-light ms-1" title="Exportar PDF">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 0h5.5L14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zM9.5 1v3a1 1 0 0 0 1 1h3l-4-4z"/></svg>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.orders.generate_invoice_download', $o->id) }}" class="btn btn-sm btn-warning ms-1 generate-download-btn" data-order-id="{{ $o->id }}">Generar y descargar PDF</a>
                                    @endif
                                    <form action="{{ route('admin.orders.destroy', $o->id) }}" method="POST" style="display:inline-block" class="needs-confirm" data-confirm-title="Eliminar pedido #{{ $o->id }}" data-confirm-message="¿Eliminar pedido #{{ $o->id }}? Esta acción no se puede deshacer." data-confirm-button="Eliminar">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger ms-1">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>

                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación moderno (reemplaza confirm()) -->
<!-- The confirmation modal is provided globally in the layout (resources/views/layouts/app.blade.php) -->

<script>
    (function(){
        // Delegate clicks on pagination links inside settings main
        function handlePaginationClick(e){
            const a = e.target.closest('#settings-main .pagination a, .settings-pagination-top .pagination a');
            if(!a) return;
            e.preventDefault();
            const url = a.href;
            // Fetch HTML and replace #settings-main content
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                .then(r => {
                    if (!r.ok) throw new Error('Network error');
                    return r.text();
                }).then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMain = doc.querySelector('#settings-main');
                    if (newMain) {
                        const current = document.querySelector('#settings-main');
                        current.innerHTML = newMain.innerHTML;
                        // update URL
                        window.history.pushState({ajax: true}, '', url);
                        // optional: focus top of results
                        current.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        // fallback to full navigation
                        window.location.href = url;
                    }
                }).catch(err => {
                    console.error('Pagination AJAX failed', err);
                    window.location.href = url;
                });
        }

        document.addEventListener('click', handlePaginationClick);


        document.addEventListener('click', function(e){
            const btn = e.target.closest('.generate-download-btn');
            if(!btn) return;
            e.preventDefault();
            const orderId = btn.getAttribute('data-order-id');
            if(!orderId) return;
            btn.disabled = true; btn.textContent = 'Generando...';

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const headers = { 'Accept': 'application/json' };
            if (csrfMeta && csrfMeta.getAttribute('content')) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');

            fetch("{{ url('/settings/orders') }}"+"/"+orderId+"/invoice/ajax", {
                method: 'POST',
                headers: headers,
                credentials: 'same-origin'
            }).then(r => {
                if (!r.ok) {
                    return r.text().then(t => {
                        let msg = t;
                        try {
                            const json = JSON.parse(t);
                            if (json.message) msg = json.message;
                        } catch(e) {}
                        throw new Error(msg);
                    });
                }
                const ct = r.headers.get('Content-Type') || '';
                if (ct.includes('application/json')) return r.json();
                
                return r.text().then(txt => {
                    try { return JSON.parse(txt); } catch (e) { throw new Error('Respuesta inválida del servidor'); }
                });
            }).then(data => {
                    if (data && data.success) {
                        try {
                            const saved = data.saved_files || [];
                            const filePath = data.file_path || '';
                            const invoiceId = data.invoice_id || null;
                            const basename = (p) => p.split('/').pop();
                            let pdf = null;
                            for (let i=0;i<saved.length;i++) {
                                if (saved[i] && saved[i].toLowerCase().endsWith('.pdf')) { pdf = saved[i]; break; }
                            }
                            if (!pdf && filePath && filePath.toLowerCase().endsWith('.pdf')) pdf = filePath;
                            if (pdf && invoiceId) {
                                window.location = "{{ url('/settings/invoices') }}" + "/" + invoiceId + "/download?file=" + encodeURIComponent(basename(pdf));
                                return;
                            }

                            
                            if (invoiceId) {
                                const exportUrl = "{{ url('/settings/orders') }}" + "/" + orderId + "/invoice/xml";
                                const tryDownload = async (attemptsLeft) => {
                                    try {
                                        const resp = await fetch(exportUrl, { method: 'GET', credentials: 'same-origin' });
                                        if (!resp.ok) throw new Error('no ok');
                                        const ct = resp.headers.get('Content-Type') || '';
                                        if (ct.includes('pdf')) {
                                            const blob = await resp.blob();
                                            const url = URL.createObjectURL(blob);
                                            const a = document.createElement('a');
                                            a.href = url;
                                            a.download = (data.invoice_number || ('comprobante-' + invoiceId)) + '.pdf';
                                            document.body.appendChild(a);
                                            a.click();
                                            a.remove();
                                            URL.revokeObjectURL(url);
                                            return true;
                                        }
                                    
                                        if (attemptsLeft > 0) {
                                            
                                            await new Promise(r => setTimeout(r, 1000));
                                            return tryDownload(attemptsLeft - 1);
                                        }
                                        return false;
                                    } catch (e) {
                                        if (attemptsLeft > 0) {
                                            await new Promise(r => setTimeout(r, 1000));
                                            return tryDownload(attemptsLeft - 1);
                                        }
                                        return false;
                                    }
                                };

                                tryDownload(3).then(foundPdf => {
                                    if (!foundPdf) {
                                        
                                        window.location = exportUrl;
                                    }
                                });
                                return;
                            }
                        } catch(e) { /* ignore and fallback to XML */ }

                        window.location = "{{ url('/settings/orders') }}"+"/"+orderId+"/invoice/xml";
                    } else {
                        showAlertOrders('Error', 'No se pudo generar la factura: ' + ((data && data.message) || 'Error'));
                        btn.disabled = false; btn.textContent = 'Generar y descargar PDF';
                    }
            }).catch(err=>{
                console.error('Error al generar factura (AJAX):', err);
                let msg = err.message || 'Error de red';
                if (msg.startsWith('Error: ')) msg = msg.substring(7);
                showAlertOrders('Error', msg);
                btn.disabled = false; btn.textContent = 'Generar y descargar PDF';
            });
        });


        window.addEventListener('popstate', function(e){
            const url = location.href;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
                .then(r => r.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMain = doc.querySelector('#settings-main');
                    if (newMain) document.querySelector('#settings-main').innerHTML = newMain.innerHTML;
                }).catch(()=>{ /* ignore, let native navigation handle it */ });
        });
    })();
</script>

<!-- Modal genérico de alertas  -->
<div class="modal fade" id="ordersAlertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content orders-modal">
            <div class="modal-header" style="border-bottom:none;">
                <div style="display:flex; align-items:center; gap:.6rem">
                    <div style="width:44px; height:44px; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#7c3aed,#06b6d4); border-radius:10px; box-shadow:0 6px 18px rgba(6,11,40,0.12); color:white;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm.93-9.412-1 4a.5.5 0 0 1-.97 0l-1-4A.5.5 0 0 1 6.5 5h3a.5.5 0 0 1 .43.588zM8 4.5a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5z"/></svg>
                    </div>
                    <h5 class="modal-title" id="ordersAlertModalTitle">Atención</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="ordersAlertModalBody" style="padding:1.6rem 1.6rem; font-size:0.98rem; color:#0f172a; background:linear-gradient(180deg,#fff,#f8fafc);">
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern styles for orders alert modal */
#ordersAlertModal .modal-dialog { max-width:520px; }
#ordersAlertModal .modal-content.orders-modal { border-radius:14px; overflow:hidden; box-shadow:0 12px 40px rgba(2,6,23,0.28); border:0; }
#ordersAlertModal .modal-title { margin:0; font-weight:700; font-size:1.05rem; color:#0f172a; }
#ordersAlertModal .modal-body { color:#0f172a; }
#ordersAlertModal .modal-footer { background:transparent; padding:1rem 1.4rem; }
#ordersAlertModal .btn-primary { background: linear-gradient(90deg,#6366f1,#06b6d4); border:0; box-shadow:0 6px 18px rgba(99,102,241,0.18); }
#ordersAlertModal .btn-close { filter:grayscale(1) opacity(.6); }
</style>
<script>
    function showAlertOrders(title, message){
        try {
            var modalEl = document.getElementById('ordersAlertModal');
            if (!modalEl) throw new Error('no-modal');
            var mt = document.getElementById('ordersAlertModalTitle');
            var mb = document.getElementById('ordersAlertModalBody');
            if (mt) mt.textContent = title || 'Atención';
            if (mb) mb.textContent = message || '';
            var bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } catch (e) {
            alert(message || title || 'Atención');
        }
    }
</script>

@endsection
</x-app-layout>
