<x-app-layout>
    @section('content')
    <div class="container-fluid py-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-gray-800">Centro de Notificaciones</h1>
                <p class="text-muted mb-0">Gestión de alertas y tareas pendientes.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-dark border shadow-sm p-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bell-fill text-warning me-1" viewBox="0 0 16 16">
                        <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z"/>
                    </svg>
                    {{ ($pendingYape->count() ?? 0) + ($lowStock->count() ?? 0) }} Pendientes
                </span>
            </div>
        </div>

        <div class="row g-4">
            <!-- Pedidos por Yape -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary" style="background-color: rgba(13, 110, 253, 0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-phone" viewBox="0 0 16 16">
                                    <path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
                                    <path d="M8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                                </svg>
                            </div>
                            <h5 class="mb-0 fw-semibold">Validación de Pagos (Yape)</h5>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $pendingYape->count() }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($pendingYape->isEmpty())
                            <div class="text-center d-flex flex-column align-items-center justify-content-center py-5 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check-circle text-light mb-3" viewBox="0 0 16 16" style="color: #dee2e6 !important;">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M10.97 4.97a.235.235 0 0 0-.02.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/>
                                </svg>
                                <p>No hay pagos pendientes de validación.</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($pendingYape as $order)
                                    <div class="list-group-item px-4 py-3 border-bottom-0 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="text-center">
                                                    <div class="small text-muted text-uppercase" style="font-size: 0.7rem;">Pedido</div>
                                                    <div class="fw-bold">#{{ $order->id }}</div>
                                                </div>
                                                <div class="vr mx-1"></div>
                                                <div>
                                                    <div class="fw-medium text-dark">{{ $order->user->name ?? ($order->customer_name ?? 'Cliente Invitado') }}</div>
                                                    <div class="small text-muted d-flex align-items-center gap-2">
                                                        <span>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-clock me-1" viewBox="0 0 16 16">
                                                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                                            </svg>
                                                            {{ $order->created_at->diffForHumans() }}
                                                        </span>
                                                        <span>•</span>
                                                        <span class="text-primary fw-medium">S/. {{ number_format($order->total, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                @php
                                                    $statusColors = [
                                                        'pendiente' => 'warning',
                                                        'pagado' => 'success',
                                                        'cancelado' => 'secondary',
                                                        'fallido' => 'danger',
                                                        'pending' => 'warning'
                                                    ];
                                                    $st = strtolower($order->status);
                                                    $color = $statusColors[$st] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }} border-opacity-25 rounded-pill px-3" style="background-color: var(--bs-{{ $color }}-bg-subtle); color: var(--bs-{{ $color }}-text-emphasis);">
                                                    {{ ucfirst($st) }}
                                                </span>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light border shadow-sm text-primary">
                                                    Revisar 
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-right ms-1" viewBox="0 0 16 16">
                                                        <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="p-3 text-center border-top bg-light">
                                <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-primary fw-medium small">Ver todos los pedidos</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bajo Stock -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-danger bg-opacity-10 p-2 rounded-circle text-danger" style="background-color: rgba(220, 53, 69, 0.1);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1 1 0 0 1-.629.928l-7.185 2.874a.5.5 0 0 1-.372 0L.63 13.09a1 1 0 0 1-.63-.928V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                                </svg>
                            </div>
                            <h5 class="mb-0 fw-semibold">Alerta de Stock Bajo</h5>
                        </div>
                        <span class="badge bg-danger rounded-pill">{{ $lowStock->count() ?? 0 }}</span>
                    </div>
                    <div class="card-body p-0">
                        @if($lowStock->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-check2-all text-light mb-3" viewBox="0 0 16 16" style="color: #dee2e6 !important;">
                                    <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"/>
                                    <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"/>
                                </svg>
                                <p>Inventario saludable.</p>
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($lowStock as $prod)
                                    <div class="list-group-item px-4 py-3 border-bottom-0 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-light rounded p-1 border" style="width: 48px; height: 48px;">
                                                    @if($prod->image)
                                                        @php
                                                            $img = $prod->image;
                                                            if (!preg_match('#^https?://#', $img)) {
                                                                $img = asset('storage/' . ltrim($img, '/'));
                                                            }
                                                        @endphp
                                                        <img src="{{ $img }}" class="w-100 h-100 object-fit-cover rounded" alt="" style="object-fit: cover;">
                                                    @else
                                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted small">Img</div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-medium text-dark text-truncate" style="max-width: 200px;">{{ $prod->name }}</div>
                                                    <div class="small text-danger fw-bold">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-exclamation-triangle me-1" viewBox="0 0 16 16">
                                                            <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                                            <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 4.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                                                        </svg>
                                                        Quedan: {{ $prod->stock }}
                                                    </div>
                                                </div>
                                            </div>
                                            <a href="{{ route('admin.products.edit', $prod) }}" class="btn btn-sm btn-outline-secondary">
                                                Reponer
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .page-padding { padding-top: 2rem; padding-bottom: 2rem; }
        .card { transition: transform 0.2s ease-in-out; }
        .card:hover { transform: translateY(-2px); }
        .bg-opacity-10 { --bs-bg-opacity: 0.1; }
        .object-fit-cover { object-fit: cover; }
    </style>
    @endsection
</x-app-layout>
