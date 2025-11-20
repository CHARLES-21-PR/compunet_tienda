<x-app-layout>

@section('content')

<div class="container-fluid">
	<div class="row g-0">
		<div class="col-12 col-md-3 px-0">
			@include('settings.nav_cate')
		</div>
		<div id="settings-main" class="col-12 col-md-9 ps-md-1">
			<div class="bg-dark rounded-3 p-3">
				<div class="d-flex align-items-center justify-content-between mb-3">
					<h1 class="text-white mb-0">Pedido #{{ $order->id }}</h1>
					<div>
						<a href="{{ route('settings.orders.index') }}" class="btn btn-outline-light btn-sm">Volver</a>
					</div>
				</div>

				<div class="row">
					<div class="col-12 col-md-6">
						<h5 class="text-white">Resumen</h5>
						@if(session('success'))
							<div class="alert alert-success">{{ session('success') }}</div>
						@endif
						@if(session('error'))
							<div class="alert alert-danger">{{ session('error') }}</div>
						@endif
						<ul class="list-unstyled text-white-50">
							<li><strong>ID:</strong> {{ $order->id }}</li>
							@php
								$statusMap = [
									'pagado' => ['label' => 'Pagado', 'class' => 'success'],
									'entregado' => ['label' => 'Entregado', 'class' => 'primary'],
									'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
									'fallido' => ['label' => 'Fallido', 'class' => 'danger'],
									'pendiente' => ['label' => 'Pendiente', 'class' => 'warning'],
								];
								$st = strtolower($order->status ?? '');
								$aliases = [
									'paid' => 'pagado', 'delivered' => 'entregado', 'cancelled' => 'cancelado', 'failed' => 'fallido', 'pending' => 'pendiente'
								];
								if (isset($aliases[$st])) $st = $aliases[$st];
								$badge = $statusMap[$st] ?? ['label' => ucfirst($st ?: 'Pendiente'), 'class' => 'secondary'];
							@endphp
							<li><strong>Estado actual:</strong>
								<span class="badge bg-{{ $badge['class'] }} @if($badge['class']==='warning' || $badge['class']==='info') text-dark @endif">{{ $badge['label'] }}</span>
							</li>
							<li><strong>Total:</strong> S/ {{ number_format($order->total,2) }}</li>
							<li><strong>Usuario:</strong> {{ $order->user_id ? 'Usuario #'.$order->user_id : 'Invitado' }}</li>
							<li><strong>Creado:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</li>
						</ul>

						<form action="{{ route('settings.orders.update', $order->id) }}" method="POST" class="mt-3">
							@csrf
							@method('PUT')
							<div class="input-group">
								<select name="status" class="form-select form-select-sm">
									@php
										$states = [
											'pagado' => 'Pagado',
											'entregado' => 'Entregado',
											'cancelado' => 'Cancelado',
											'fallido' => 'Fallido',
											'pendiente' => 'Pendiente'
										];
									@endphp
									@foreach($states as $k => $label)
										<option value="{{ $k }}" {{ ($order->status === $k) || (empty($order->status) && $k === 'pendiente') ? 'selected' : '' }}>{{ $label }}</option>
									@endforeach
								</select>
								<button class="btn btn-sm btn-primary" type="submit">Actualizar estado</button>
							</div>
						</form>
					</div>
					<div class="col-12 col-md-6">
						<h5 class="text-white">Pago / Factura</h5>
						@php
							$payment = $order->payments()->orderBy('id','desc')->first();
							$invoice = $order->invoice;
							$paymentMeta = [];
							$receiptPath = null;
							if (!empty($payment) && !empty($payment->metadata)) {
								$paymentMeta = json_decode($payment->metadata, true) ?: [];
								$receiptPath = $paymentMeta['receipt_path'] ?? null;
							}
						@endphp
						@if($payment)
							<p class="text-white-50 mb-1"><strong>Transacción:</strong> {{ $payment->transaction_id ?? '—' }}</p>
							<p class="text-white-50 mb-1"><strong>Método:</strong> {{ $payment->method ?? '—' }}</p>
							<p class="text-white-50 mb-1"><strong>Monto:</strong> S/ {{ number_format($payment->amount,2) }}</p>
							@if($receiptPath)
								@php $url = asset('storage/' . ltrim($receiptPath, '/')); @endphp
								<p class="text-white-50 mb-1"><strong>Comprobante:</strong> <a href="{{ $url }}" target="_blank" class="link-light">Ver comprobante</a></p>
							@endif
						@endif
						@if($invoice)
							<p class="text-white-50 mb-1"><strong>Factura / Boleta:</strong> {{ $invoice->invoice_number ?? ('#'.$invoice->id) }}</p>
							<button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="collapse" data-bs-target="#invoiceData" aria-expanded="false">Ver datos de factura</button>
							<div class="collapse mt-2" id="invoiceData">
								<pre class="bg-dark text-white p-2" style="max-height:240px;overflow:auto;">{{ json_encode(json_decode($invoice->data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
							</div>
						@endif

						{{-- Formulario para (re)generar factura --}}
						<form action="{{ route('settings.orders.generate_invoice', $order->id) }}" method="POST" class="mt-2">
							@csrf
							<button class="btn btn-sm btn-primary" type="submit">Generar / Re-generar factura</button>
						</form>

						@php
							$xmlAvailable = false;
							$savedFiles = [];
							if(!empty($invoice)){
								$data = json_decode($invoice->data, true) ?: [];
								$savedFiles = $data['saved_files'] ?? [];
								foreach($savedFiles as $f){
									if(str_ends_with(strtolower($f), '.xml')) $xmlAvailable = true;
								}
								if(!empty($data['response']) && is_array($data['response'])){
									$resp = $data['response'];
									if(!empty($resp['xml_base64']) || !empty($resp['xml_zip_base64'])) $xmlAvailable = true;
								}
								if(!$xmlAvailable && !empty($invoice->file_path) && str_ends_with(strtolower($invoice->file_path), '.xml')) $xmlAvailable = true;
							}
						@endphp

						@if(!empty($invoice) && !empty($savedFiles))
							<div class="mt-2">
								@foreach($savedFiles as $sf)
									@php $bn = basename($sf); $bn_noext = pathinfo($bn, PATHINFO_FILENAME); @endphp
									<a href="{{ route('settings.invoices.download', $invoice->id) }}?file={{ urlencode($bn) }}" class="btn btn-sm btn-outline-primary me-1" title="Abrir {{ $bn_noext }}">{{ $bn_noext }}</a>
								@endforeach
							</div>
						@endif

						@php
							$pdfAvailable = false;
							if(!empty($invoice)){
								$d = json_decode($invoice->data, true) ?: [];
								$sf = $d['saved_files'] ?? [];
								foreach($sf as $f){ if(str_ends_with(strtolower($f), '.pdf')) { $pdfAvailable = true; break; } }
								if(!$pdfAvailable && !empty($invoice->file_path) && str_ends_with(strtolower($invoice->file_path), '.pdf')) $pdfAvailable = true;
								if(!$pdfAvailable && !empty($d['response']) && is_array($d['response'])){ $r = $d['response']; if(!empty($r['pdf_base64']) || !empty($r['enlace_del_pdf']) || !empty($r['enlace_pdf'])) $pdfAvailable = true; }
							}
						@endphp
						@if($pdfAvailable)
							<a href="{{ route('settings.orders.export_xml', $order->id) }}" class="btn btn-sm btn-success mt-2" title="Exportar PDF">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 0h5.5L14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zM9.5 1v3a1 1 0 0 0 1 1h3l-4-4z"/></svg>
							</a>
						@else
							<button id="generateDownloadBtn" data-order-id="{{ $order->id }}" class="btn btn-sm btn-warning mt-2">Generar y descargar PDF</button>
							<p id="generateMsg" class="text-white-50 mt-2 small" style="display:none"></p>
						@endif
					</div>
				</div>

				<hr class="border-secondary">

				<h5 class="text-white">Items</h5>
				<div class="table-responsive">
					<table class="table table-dark rounded-3 mb-0">
						<thead>
						<tr>
							<th>Producto</th>
							<th>Precio</th>
							<th>Cantidad</th>
						</tr>
						</thead>
						<tbody>
						@foreach($order->items as $it)
						<tr>
							<td>{{ $it->name }}</td>
							<td>{{ number_format($it->price,2) }}</td>
							<td>{{ $it->quantity }}</td>
						</tr>
						@endforeach
						</tbody>
					</table>
				</div>

			</div>
		</div>
	</div>
</div>

<!-- Modal genérico de alertas para settings/orders (ubicado en body para ser accesible) -->
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

<script>
document.addEventListener('DOMContentLoaded', function(){
	const btn = document.getElementById('generateDownloadBtn');
	if (!btn) return;
	btn.addEventListener('click', function(e){
		const orderId = btn.getAttribute('data-order-id');
		if (!orderId) return;
		btn.disabled = true; btn.textContent = 'Generando...';
		const msg = document.getElementById('generateMsg'); if(msg) { msg.style.display='none'; }
		fetch("{{ url('/settings/orders') }}/"+orderId+"/invoice/ajax", {
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
				'Accept': 'application/json'
			},
			credentials: 'same-origin'
		}).then(r => r.json()).then(data => {
					if (data.success) {
						// Prefer descargar PDF si Nubefact lo entregó (saved_files o file_path)
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
						} catch(e) { /* ignore and fallback to XML */ }
						window.location = "{{ url('/settings/orders') }}"+"/"+orderId+"/invoice/xml";
					} else {
						btn.disabled = false; btn.textContent = 'Generar y descargar XML';
						if (msg) { msg.style.display='block'; msg.textContent = 'No se pudo generar: ' + (data.message || 'Error'); }
						else showAlertOrders('Error', 'No se pudo generar: ' + (data.message || 'Error'));
			}
		}).catch(err => {
					btn.disabled = false; btn.textContent = 'Generar y descargar XML';
					if (msg) { msg.style.display='block'; msg.textContent = 'Error de red al generar factura'; }
					else showAlertOrders('Error', 'Error de red al generar factura');
		});
	});
});
</script>
