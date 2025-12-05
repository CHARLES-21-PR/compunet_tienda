<x-app-layout>

@section('content')
<div class="container-fluid">
    <div class="row g-0">
        <div class="col-12 col-md-3 px-0">
            @include('admin.partials.nav_cate')
        </div>
        <div class="col-12 col-md-9 ps-md-1">
            <div class="bg-dark rounded-3 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="text-white mb-0">Editar Pedido #{{ $order->id }}</h1>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <div class="card bg-secondary text-white border-0 mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Detalles del Pedido</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Cliente:</strong> {{ $order->user ? $order->user->name : 'Invitado' }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $order->user ? $order->user->email : 'N/A' }}</p>
                                <p class="mb-1"><strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
                                <p class="mb-1"><strong>Método de Pago:</strong> {{ ucfirst($order->payment_method ?? 'N/A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label text-white">Estado del Pedido</label>
                            <select name="status" id="status" class="form-select bg-dark text-white border-secondary">
                                @php
                                    $statuses = [
                                        'pendiente' => 'Pendiente',
                                        'pagado' => 'Pagado',
                                        'entregado' => 'Entregado',
                                        'cancelado' => 'Cancelado',
                                        'fallido' => 'Fallido'
                                    ];
                                    $currentStatus = strtolower($order->status);
                                @endphp
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ $currentStatus == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="total" class="form-label text-white">Total</label>
                            <input type="number" step="0.01" name="total" id="total" class="form-control bg-dark text-white border-secondary" value="{{ old('total', $order->total) }}">
                            @error('total')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label text-white">Método de Pago</label>
                            <select name="payment_method" id="payment_method" class="form-select bg-dark text-white border-secondary">
                                <option value="">Seleccionar...</option>
                                @php
                                    $methods = ['yape', 'plin', 'transferencia', 'efectivo', 'tarjeta'];
                                    $currentMethod = strtolower($order->payment_method ?? '');
                                @endphp
                                @foreach($methods as $method)
                                    <option value="{{ $method }}" {{ $currentMethod == $method ? 'selected' : '' }}>
                                        {{ ucfirst($method) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_method')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="created_at" class="form-label text-white">Fecha del Pedido</label>
                            <input type="datetime-local" name="created_at" id="created_at" class="form-control bg-dark text-white border-secondary" value="{{ old('created_at', $order->created_at->format('Y-m-d\TH:i')) }}">
                            @error('created_at')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Pedido</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

</x-app-layout>
