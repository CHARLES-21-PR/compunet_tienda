<x-app-layout>
	<div class="container max-w-7xl mx-auto py-4 px-3">
		<div class="flex items-center justify-between mb-6">
			<div>
				<h1 class="text-2xl font-semibold text-gray-900">Mis pedidos</h1>
				<p class="mt-1 text-sm text-gray-600">Aquí puedes ver el estado de tus compras y los detalles básicos de cada pedido.</p>
			</div>
		</div>

		@php
			$orders = \App\Models\Order::where('user_id', auth()->id())->with('items')->orderBy('created_at','desc')->get();
		@endphp
		@php
		
			$orders = \App\Models\Order::where('user_id', auth()->id())
				->with(['items.product', 'payments', 'invoice'])
				->orderBy('created_at','desc')->get();
		@endphp

		@if($orders->isEmpty())
			<div class="bg-white shadow rounded-lg p-6 text-center">
				<h2 class="text-lg font-medium text-gray-800">Aún no tienes pedidos</h2>
				<p class="mt-2 text-sm text-gray-500">Explora nuestra tienda y realiza tu primera compra.</p>
				<div class="mt-4">
					<a href="{{ route('categories.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Ver productos</a>
				</div>
			</div>
		@else
			<div class="mb-4">
				<h3 class="text-sm font-semibold text-gray-700">Leyenda de estados</h3>
				<div class="mt-3">
					@php
						
						try {
							$dbStatuses = \App\Models\OrderStatus::orderBy('id')->get();
						} catch (\Throwable $e) {
							$dbStatuses = null;
						}

						$legend = [
							'pendiente' => ['label' => 'Pendiente', 'gradient' => 'linear-gradient(90deg,#FDE68A,#F59E0B)', 'text' => '#92400E'],
							'pagado' => ['label' => 'Pagado', 'gradient' => 'linear-gradient(90deg,#86EFAC,#16A34A)', 'text' => '#064E3B'],
							'entregado' => ['label' => 'Entregado', 'gradient' => 'linear-gradient(90deg,#BFDBFE,#3B82F6)', 'text' => '#1E3A8A'],
							'cancelado' => ['label' => 'Cancelado', 'gradient' => 'linear-gradient(90deg,#FBCFE8,#F472B6)', 'text' => '#701A75'],
							'fallido' => ['label' => 'Fallido', 'gradient' => 'linear-gradient(90deg,#FECACA,#EF4444)', 'text' => '#7F1D1D'],
						];

						$items = [];
						if (!empty($dbStatuses) && $dbStatuses->isNotEmpty()) {
							foreach ($dbStatuses as $s) {
								$items[] = ['label' => $s->label, 'gradient' => ($s->bg ?? 'linear-gradient(90deg,#E5E7EB,#CBD5E1)'), 'text' => ($s->text_color ?? '#111827')];
							}
						} else {
							foreach ($legend as $k => $v) {
								$items[] = ['label' => $v['label'], 'gradient' => $v['gradient'], 'text' => $v['text']];
							}
						}
					@endphp

					<div class="d-flex flex-wrap gap-2">
						@foreach($items as $it)
							<div class="d-inline-flex align-items-center px-3 py-1 rounded-pill" style="background: {{ $it['gradient'] }}; color: {{ $it['text'] }}; box-shadow: 0 6px 18px rgba(15,23,42,0.06);">
								<span style="width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:8px;box-shadow:0 2px 6px rgba(0,0,0,0.08);background:rgba(255,255,255,0.85);"></span>
								<span style="font-weight:600; font-size:0.85rem;">{{ $it['label'] }}</span>
							</div>
						@endforeach
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 gap-6">
				@foreach($orders as $order)
					@php
						$itemCount = $order->items->sum('quantity');
						$subtotal = $order->items->sum(function($it){ return ($it->price ?? 0) * ($it->quantity ?? 1); });
						$igv = $order->total_igv ?? null;
						$total = $order->total ?? ($subtotal + ($igv ?? 0));
						
						if (empty($igv) || floatval($igv) == 0) {
							if (floatval($total) > 0) {
								$derivedSubtotal = round($total / 1.18, 2);
								$derivedIgv = round($total - $derivedSubtotal, 2);
							} else {
								$derivedSubtotal = $subtotal;
								$derivedIgv = 0;
							}
						} else {
							$derivedSubtotal = $subtotal;
							$derivedIgv = $igv;
						}
						$status = strtolower($order->status ?? 'pending');
						$statusDefs = config('orders.statuses', []);
						$def = $statusDefs[$status] ?? null;
						$statusLabel = $def['label'] ?? (ucfirst($status));
						$statusBg = $def['bg'] ?? 'bg-gray-50';
						$statusText = $def['text'] ?? 'text-gray-800';
						$statusBorder = $def['border'] ?? 'border-gray-300';
						
						$statusRaw = strtolower($order->status ?? 'pendiente');
						$statusMap = [
							'pendiente' => ['label' => 'Pendiente', 'bg' => 'bg-warning', 'text' => 'text-dark', 'border' => 'border-warning'],
							'pagado' => ['label' => 'Pagado', 'bg' => 'bg-success', 'text' => 'text-white', 'border' => 'border-success'],
							'fallido' => ['label' => 'Fallido', 'bg' => 'bg-danger', 'text' => 'text-white', 'border' => 'border-danger'],
							'cancelado' => ['label' => 'Cancelado', 'bg' => 'bg-secondary', 'text' => 'text-white', 'border' => 'border-secondary'],
							'entregado' => ['label' => 'Entregado', 'bg' => 'bg-primary', 'text' => 'text-white', 'border' => 'border-primary'],
						];
						$stDef = $statusMap[$statusRaw] ?? ['label' => ucfirst($statusRaw), 'bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'border' => 'border-gray-300'];
						$statusLabel = $stDef['label'];
						$statusBg = $stDef['bg'];
						$statusText = $stDef['text'];
						$statusBorder = $stDef['border'];
					@endphp

					<div class="bg-white shadow rounded-lg overflow-hidden card mb-3">
						<div class="p-3 p-md-4 d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 card-body">
							<div class="flex items-center gap-4 flex-1">
								<div class="w-20 h-20 bg-gray-100 rounded overflow-hidden flex items-center justify-center border-l-4 {{ $statusBorder }}" style="width:80px;height:80px;">
									@php
										$first = $order->items->first();
										$img = null;
										if ($first && $first->product && $first->product->image) {
											$raw = $first->product->image;
											
											if (preg_match('/^https?:\/\//', $raw)) {
												$img = $raw;
											} else {
												$candidate = public_path('storage/' . ltrim($raw, '/'));
												if (file_exists($candidate)) {
													$img = asset('storage/' . ltrim($raw, '/'));
												} else {
													$pub = public_path(ltrim($raw, '/'));
													if (file_exists($pub)) {
														$img = asset(ltrim($raw, '/'));
													}
												}
											}
										}
									@endphp
									@if($img)
										<img src="{{ $img }}" alt="{{ $first->name ?? 'Producto' }}" class="w-100 h-100 object-cover img-fluid rounded">
									@else
										<svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="36" height="36"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3v18h18"></path></svg>
									@endif
								</div>

								<div class="min-w-0">
									<div class="flex items-center gap-2">
										<div class="text-xs text-gray-500">Pedido</div>
										<div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
										<div class="text-sm text-gray-500">· {{ $order->created_at->format('d M Y') }}</div>
									</div>

									<div class="mt-2 text-sm text-gray-600">
										<div class="truncate">
											@foreach($order->items->take(3) as $it)
												<span class="d-inline-block me-2">{{ $it->quantity }}x {{ \Illuminate\Support\Str::limit($it->name, 30) }}</span>
											@endforeach
											@if($order->items->count() > 3)
												<span class="text-muted">(+{{ $order->items->count() - 3 }} más)</span>
											@endif
										</div>
										<div class="mt-2">Total: <strong>S/ {{ number_format($total,2) }}</strong></div>
										
									</div>
								</div>
							</div>

							<div class="d-flex align-items-center gap-2">
								<span class="badge rounded-pill me-2 {{ $statusBg }} {{ $statusText }}">{{ $statusLabel }}</span>

								<a href="{{ route('checkout.success', $order->id) }}" class="btn btn-sm btn-outline-primary me-2">Ver</a>

								@php
									
									$yapeReceipt = null;
									foreach($order->payments ?? [] as $p) {
										$meta = null;
										try { $meta = is_string($p->metadata) ? json_decode($p->metadata, true) : $p->metadata; } catch (\Throwable $e) { $meta = null; }
										if (!empty($meta) && !empty($meta['receipt_path'])) { $yapeReceipt = $meta['receipt_path']; break; }
									}
								@endphp

								@if($order->invoice)
									@php
										$invData = json_decode($order->invoice->data, true) ?: [];
										$savedFiles = $invData['saved_files'] ?? [];
										$pdfButtonsRendered = false;
									@endphp
									@if(!empty($savedFiles))
										<div class="btn-group">
											@foreach($savedFiles as $sf)
												@php $bn = basename($sf); $bn_noext = pathinfo($bn, PATHINFO_FILENAME); @endphp
												<a href="{{ route('client.orders.invoice.download', $order->id) }}?file={{ urlencode($bn) }}" class="btn btn-sm btn-outline-primary" title="Descargar {{ $bn_noext }}">{{ $bn_noext }}</a>
												@php if (str_ends_with(strtolower($sf), '.pdf')) $pdfButtonsRendered = true; @endphp
											@endforeach
										</div>
									@endif

									@if(!$pdfButtonsRendered && !empty($order->invoice->file_path) && str_ends_with(strtolower($order->invoice->file_path), '.pdf'))
										@php $fileBn = basename($order->invoice->file_path); @endphp
										<a href="{{ route('client.orders.invoice.download', $order->id) }}?file={{ urlencode($fileBn) }}" class="btn btn-sm btn-success" title="Descargar PDF">
											<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 0h5.5L14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zM9.5 1v3a1 1 0 0 0 1 1h3l-4-4z"/></svg>
										</a>
									@elseif(!$pdfButtonsRendered)
										@if($yapeReceipt)
											@php
												$receiptPath = $yapeReceipt;
										
												if (preg_match('#^https?://#i', $receiptPath)) {
													$receiptUrl = $receiptPath;
												} else {
													$try1 = public_path('storage/' . ltrim($receiptPath, '/'));
													$try2 = public_path(ltrim($receiptPath, '/'));
													if (file_exists($try1)) {
														$receiptUrl = asset('storage/' . ltrim($receiptPath, '/'));
													} elseif (file_exists($try2)) {
														$receiptUrl = asset(ltrim($receiptPath, '/'));
													} else {
														
														$receiptUrl = asset('storage/' . ltrim($receiptPath, '/'));
													}
												}
												$receiptBn = basename($receiptPath);
											@endphp
											<a href="{{ $receiptUrl }}" target="_blank" class="btn btn-sm btn-outline-success me-2">Ver voucher</a>
										@else
											<button disabled class="btn btn-sm btn-light text-muted">Comprobante</button>
										@endif
									@endif
									@else
										<button disabled class="btn btn-sm btn-light text-muted">Comprobante</button>
									@endif
							
							</div>
						</div>

						<div class="border-t bg-gray-50 px-4 py-3 text-sm text-gray-600">
											<div class="flex items-center gap-4">
												<div>Ítems: <strong>{{ $itemCount }}</strong></div>
												<div>Subtotal: S/ {{ number_format($derivedSubtotal,2) }}</div>
												<div>IGV: S/ {{ number_format($derivedIgv,2) }}</div>
											</div>
										</div>
					</div>
				@endforeach
			</div>
		@endif

	</div>
</x-app-layout>