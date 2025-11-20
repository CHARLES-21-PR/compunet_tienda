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
					<h1 class="text-white mb-0">Pedidos</h1>
					<div>
						<form id="ordersFilterForm" method="GET" action="{{ route('settings.orders.index') }}" class="d-flex align-items-center gap-2">
							<select id="ordersStatusSelect" name="status" class="form-select form-select-sm" style="height:40px; min-width: 180px;" onchange="document.getElementById('ordersFilterForm').submit();">
								<option value="">Todos los estados</option>
								@foreach($availableStatuses ?? [] as $k => $label)
									<option value="{{ $k }}" {{ request()->query('status') === $k ? 'selected' : '' }}>{{ $label }}</option>
								@endforeach
							</select>
							@if(request()->query('status'))
								<a href="{{ route('settings.orders.index') }}" class="btn btn-sm btn-secondary">Limpiar</a>
							@endif
						</form>
					</div>
				</div>

				@if($orders->isEmpty())
					<p class="text-white">No hay pedidos.</p>
				@else
					<div class="d-flex justify-content-between align-items-center mb-2">
						<div class="settings-pagination-top w-100 d-flex justify-content-end">
							{{ $orders->links('pagination::bootstrap-5') }}
						</div>
					</div>

					<div class="table-responsive">
					<table class="table table-dark rounded-3">
						<thead>
							<tr>
								<th>ID</th>
								<th>Cliente</th>
								<th>Total</th>
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
								@php
									$statusMap = [
										'paid' => ['label' => 'Pagado', 'class' => 'success'],
										'delivered' => ['label' => 'Entregado', 'class' => 'primary'],
										'cancelled' => ['label' => 'Cancelado', 'class' => 'danger'],
										'failed' => ['label' => 'Fallido', 'class' => 'warning'],
									];
									$st = $o->status;
									$badge = $statusMap[$st] ?? ['label' => ucfirst($st), 'class' => 'secondary'];
								@endphp
								<td><span class="badge bg-{{ $badge['class'] }} @if($badge['class']==='warning') text-dark @endif">{{ $badge['label'] }}</span></td>
								<td>{{ $o->created_at->format('Y-m-d H:i') }}</td>
								<td>
									<a href="{{ route('settings.orders.show', $o->id) }}" class="btn btn-sm btn-secondary">Ver</a>
									@php
										$xmlAvailable = false;
										if(!empty($o->invoice)){
											$data = json_decode($o->invoice->data, true) ?: [];
											// check saved_files
											if(!empty($data['saved_files'])){
												foreach($data['saved_files'] as $f){
													if(str_ends_with(strtolower($f), '.xml')) { $xmlAvailable = true; break; }
												}
											}
											// check response base64
											if(!$xmlAvailable && !empty($data['response'])){
												$resp = $data['response'];
												if(!empty($resp['xml_base64']) || !empty($resp['xml_zip_base64'])) $xmlAvailable = true;
											}
											// check file_path
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
									@endphp
									@if($pdfAvailable)
										<a href="{{ route('settings.orders.export_xml', $o->id) }}" class="btn btn-sm btn-outline-light ms-1" title="Exportar PDF">
											<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 0h5.5L14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zM9.5 1v3a1 1 0 0 0 1 1h3l-4-4z"/></svg>
										</a>
									@else
										<a href="{{ route('settings.orders.generate_invoice_download', $o->id) }}" class="btn btn-sm btn-warning ms-1 generate-download-btn" data-order-id="{{ $o->id }}">Generar y descargar PDF</a>
									@endif
									<form action="{{ route('settings.orders.destroy', $o->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('¿Eliminar pedido #{{ $o->id }}? Esta acción no se puede deshacer.');">
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

		// Handle generate+download buttons from index (delegated)
		document.addEventListener('click', function(e){
			const btn = e.target.closest('.generate-download-btn');
			if(!btn) return;
			e.preventDefault();
			const orderId = btn.getAttribute('data-order-id');
			if(!orderId) return;
			btn.disabled = true; btn.textContent = 'Generando...';
			// Build headers safely (meta tag may be missing in some layouts)
			const csrfMeta = document.querySelector('meta[name="csrf-token"]');
			const headers = { 'Accept': 'application/json' };
			if (csrfMeta && csrfMeta.getAttribute('content')) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');

			fetch("{{ url('/settings/orders') }}/"+orderId+"/invoice/ajax", {
				method: 'POST',
				headers: headers,
				credentials: 'same-origin'
			}).then(r => {
				if (!r.ok) {
					// try to get text for debugging
					return r.text().then(t => { throw new Error('HTTP ' + r.status + ': ' + (t || r.statusText)); });
				}
				const ct = r.headers.get('Content-Type') || '';
				if (ct.includes('application/json')) return r.json();
				// sometimes Laravel returns HTML on error; try parse JSON otherwise throw
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

							// If no PDF declared in response, attempt to fetch the export endpoint and prefer PDF when available.
							// We'll try a few times to allow server-side PDF generation to finish.
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
										// if we got XML or other, consider retrying a few times before giving up
										if (attemptsLeft > 0) {
											// small delay and retry
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
										// fallback to original XML download (will trigger download or show XML page)
										window.location = exportUrl;
									}
								});
								return;
							}
						} catch(e) { /* ignore and fallback to XML */ }

						window.location = "{{ url('/settings/orders') }}"+"/"+orderId+"/invoice/xml";
					} else {
						alert('No se pudo generar la factura: ' + ((data && data.message) || 'Error'));
						btn.disabled = false; btn.textContent = 'Generar y descargar PDF';
					}
			}).catch(err=>{
				console.error('Error al generar factura (AJAX):', err);
				alert('Error al generar factura: ' + (err.message || 'Error de red'));
				btn.disabled = false; btn.textContent = 'Generar y descargar PDF';
			});
		});

		// support back/forward
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

@endsection
</x-app-layout>
