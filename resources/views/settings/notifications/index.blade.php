<x-app-layout>
    @section('content')
    <div class="container-fluid page-padding">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-3">Notificaciones</h1>
                <p class="text-muted mb-4">Resumen de elementos que requieren atención.</p>

                <div class="row g-3" style="margin-top:6px;">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100 notif-card">
                            <div class="card-body p-32">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title mb-1">Pedidos por Yape</h5>
                                        <p class="text-muted mb-2">Pedidos pendientes por validar pagos realizados con Yape.</p>
                                        <div class="h3">{{ $pendingYape->count() }}</div>
                                    </div>
                                    <div class="align-self-start">
                                        <a href="{{ route('settings.orders.index') }}" class="btn btn-primary">Ver pedidos</a>
                                    </div>
                                </div>

                                @if(!$pendingYape->isEmpty())
                                    <hr>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($pendingYape->take(12) as $order)
                                            <li class="d-flex justify-content-between align-items-center py-2">
                                                <div>
                                                    <div class="fw-bold">Pedido #{{ $order->id ?? '-' }}</div>
                                                    <div class="text-muted small">{{ $order->user->name ?? ($order->customer_name ?? '-') }} • {{ $order->created_at?->diffForHumans() ?? '' }}</div>
                                                </div>
                                                <a href="{{ route('settings.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Ver</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm h-100 notif-card">
                            <div class="card-body p-32">
                                <h5 class="card-title mb-1">Productos con bajo stock</h5>
                                <p class="text-muted mb-2">Revisa los productos que necesitan reposición.</p>
                                <div class="h3">{{ $lowStock->count() }}</div>
                                @if(!$lowStock->isEmpty())
                                    <hr>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($lowStock->take(12) as $prod)
                                            <li class="d-flex justify-content-between align-items-center py-2">
                                                <div>
                                                    <div class="fw-bold">{{ $prod->name }}</div>
                                                    <div class="text-muted small">Stock: {{ $prod->stock }}</div>
                                                </div>
                                                <a href="{{ route('settings.products.edit', $prod) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
</x-app-layout>
