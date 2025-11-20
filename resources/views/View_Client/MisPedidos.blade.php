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
				<div class="mt-2 flex flex-wrap gap-2">
					@php
						// Prefer dynamic statuses from DB
						try {
							$dbStatuses = \App\Models\OrderStatus::orderBy('id')->get();
						} catch (\Throwable $e) {
							$dbStatuses = null;
						}
						$defaultStatuses = ['paid' => 'Pagado', 'delivered' => 'Entregado', 'cancelled' => 'Cancelado', 'failed' => 'Fallido'];
					@endphp
					@if(!empty($dbStatuses) && $dbStatuses->isNotEmpty())
						@foreach($dbStatuses as $st)
							<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 text-gray-800 text-xs">● {{ $st->label }}</span>
						@endforeach
					@else
						@foreach($defaultStatuses as $k => $label)
							<span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 text-gray-800 text-xs">● {{ $label }}</span>
						@endforeach
					@endif
				</div>
			</div>

			<div class="grid grid-cols-1 gap-6">
				@foreach($orders as $order)
					@php
						$itemCount = $order->items->sum('quantity');
						$subtotal = $order->items->sum(function($it){ return ($it->price ?? 0) * ($it->quantity ?? 1); });
						$igv = $order->total_igv ?? 0;
						$total = $order->total ?? ($subtotal + $igv);
						$status = strtolower($order->status ?? 'pending');
						$statusDefs = config('orders.statuses', []);
						$def = $statusDefs[$status] ?? null;
						$statusLabel = $def['label'] ?? (ucfirst($status));
						$statusBg = $def['bg'] ?? 'bg-gray-50';
						$statusText = $def['text'] ?? 'text-gray-800';
						$statusBorder = $def['border'] ?? 'border-gray-300';
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
											// If the image is a full URL
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
										<button disabled class="btn btn-sm btn-light text-muted">Comprobante</button>
									@endif
								@else
									<button disabled class="btn btn-sm btn-light text-muted">Comprobante</button>
								@endif
							</div>
						</div>

						<div class="border-t bg-gray-50 px-4 py-3 text-sm text-gray-600">
							<div class="flex items-center gap-4">
								<div>Ítems: <strong>{{ $itemCount }}</strong></div>
								<div>Subtotal: S/ {{ number_format($subtotal,2) }}</div>
								<div>IGV: S/ {{ number_format($igv,2) }}</div>
							</div>
						</div>
					</div>
				@endforeach
			</div>
		@endif

	</div>
</x-app-layout>