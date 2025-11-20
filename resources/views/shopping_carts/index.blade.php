<x-app-layout>
    @section('content')
        <div class="container my-4">
            <div class="row">
                <div class="col-12">
                    <h1 class="h3 mb-2">Carrito de Compras</h1>
                    <p class="text-muted mb-4">Revisa los productos que añadiste. Puedes actualizar cantidades o continuar con la compra.</p>
                </div>
            </div>

            @php
                $cart = session('cart', []);
                $total = 0;
                // Normalize cart items into array of arrays for rendering
                $items = [];
                if (is_array($cart)) {
                    foreach ($cart as $key => $it) {
                        $quantity = isset($it['quantity']) ? (int)$it['quantity'] : (isset($it->quantity) ? (int)$it->quantity : 1);
                        $price = isset($it['price']) ? (float)$it['price'] : (isset($it->product->price) ? (float)$it->product->price : 0);
                        $name = $it['name'] ?? ($it['title'] ?? (isset($it->product->name) ? $it->product->name : 'Producto'));
                        // Normalizar ruta de imagen: puede venir como URL absoluta, ruta iniciando en '/', o solo el path dentro de storage
                        $rawImage = $it['image'] ?? ($it['image_url'] ?? (isset($it->product->image) ? $it->product->image : null));
                        if ($rawImage) {
                            // si es URL absoluta (http(s)://) o comienza con '/', úsala tal cual
                            // usar delimitador # para evitar escapar slashes y detectar http(s):// o protocol-relative //
                            if (preg_match('#^(https?:)?//#i', $rawImage) || strpos($rawImage, '/') === 0) {
                                $image = $rawImage;
                            } else {
                                // limpiar prefijo storage/ si llega repetido y generar URL pública via asset('storage/...')
                                $img = preg_replace('#^storage\\\/#', '', $rawImage);
                                $image = asset('storage/' . $img);
                            }
                        } else {
                            $image = asset('img/c1.webp');
                        }
                        $subtotal = $quantity * $price;
                        $total += $subtotal;
                        $items[] = ['key' => $key, 'name' => $name, 'image' => $image, 'price' => $price, 'quantity' => $quantity, 'subtotal' => $subtotal];
                    }
                }
            @endphp

            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            @guest
                                <div class="alert alert-warning d-flex align-items-center" role="alert">
                                    <div class="me-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                                            <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14z"/>
                                            <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zm.1-6.995a.905.905 0 0 1 1.8 0l-.35 4.5a.55.55 0 0 1-1.1 0l-.35-4.5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        Estás viendo el carrito como invitado. <a href="{{ route('login') }}" class="fw-semibold ms-1">Iniciar sesión</a> o <a href="{{ route('register') }}" class="fw-semibold">Registrarse</a> para guardar tu carrito en tu cuenta.
                                    </div>
                                </div>
                            @endguest

                            @if (count($items) === 0)
                                <div class="text-center py-5">
                                    
                                    <h4 class="mb-2">Tu carrito está vacío</h4>
                                    <p class="text-muted mb-3">Explora nuestros productos y añade lo que necesites.</p>
                                    <a href="{{ route('dashboard') ?? url('/') }}" class="btn btn-primary">Ir a la tienda</a>
                                </div>
                            @else
                                <div class="list-group list-group-flush">
                                    @foreach ($items as $it)
                                        <div class="list-group-item py-3">
                                            <div class="d-flex gap-3 align-items-center">
                                                <img src="{{ $it['image'] }}" alt="{{ $it['name'] }}" style="width:96px;height:96px;object-fit:cover;border-radius:8px">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h5 class="mb-1">{{ $it['name'] }}</h5>
                                                            <div class="text-muted">Precio unitario: <strong>${{ number_format($it['price'], 2) }}</strong></div>
                                                        </div>
                                                        <div class="text-end">
                                                            <div class="mb-2 text-muted">Subtotal</div>
                                                            <div class="fw-bold">${{ number_format($it['subtotal'], 2) }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center mt-3">
                                                        <div class="input-group input-group-sm" style="width:120px">
                                                            <button class="btn btn-outline-secondary btn-decrease" data-key="{{ $it['key'] }}" type="button">−</button>
                                                            <input type="text" class="form-control text-center qty-input" data-key="{{ $it['key'] }}" value="{{ $it['quantity'] }}" />
                                                            <button class="btn btn-outline-secondary btn-increase" data-key="{{ $it['key'] }}" type="button">+</button>
                                                        </div>

                                                        <button class="btn btn-link text-danger ms-3 btn-remove" data-key="{{ $it['key'] }}">Eliminar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top:20px">
                        <div class="card-body">
                            <h5 class="mb-3">Resumen de la orden</h5>
                            @php
                                // IGV (18%) applied on subtotal
                                $igv = round($total * 0.18, 2);
                                $grandTotal = $total + $igv;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <div class="text-muted">Subtotal</div>
                                <div class="fw-bold">${{ number_format($total, 2) }}</div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <div class="text-muted">IGV (18%)</div>
                                <div class="fw-bold">${{ number_format($igv, 2) }}</div>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="text-muted">Envío</div>
                                <div class="fw-bold">Gratis</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="fw-semibold">Total</div>
                                <div class="h5">${{ number_format($grandTotal, 2) }}</div>
                            </div>
                            <button class="btn btn-primary w-100 mb-2" id="checkoutBtn" {{ $total == 0 ? 'disabled' : '' }}>Proceder al pago</button>
                            <a href="{{ route('settings.products.index') ?? url('/') }}" class="btn btn-outline-secondary w-100">Seguir comprando</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Estilos rápidos para vista de carrito */
            .card { border: none; }
            .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; }
            @media (max-width: 767px) {
                .sticky-top { position: static !important; }
            }
        </style>

        <script>
            (function(){
                function recalcAndDispatch(){
                    // recalc total from DOM and dispatch cart:updated
                    var rows = document.querySelectorAll('.list-group-item');
                    var total = 0;
                    rows.forEach(function(r){
                        var subtotalEl = r.querySelector('.fw-bold');
                        var subtotal = 0;
                        if(subtotalEl){
                            subtotal = parseFloat(subtotalEl.textContent.replace(/[^0-9\.]/g, '')) || 0;
                        }
                        total += subtotal;
                    });
                    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: 0, total: total } }));
                }

                // Hook buttons (client-side only) — your backend should implement appropriate routes to persist.
                document.addEventListener('click', function(e){
                        if(e.target.matches('.btn-increase') || e.target.matches('.btn-decrease')){
                            var key = e.target.dataset.key;
                            var input = document.querySelector('.qty-input[data-key="'+key+'"]');
                            if(!input) return;
                            var newQty = parseInt(input.value || 0);
                            if(e.target.matches('.btn-increase')) newQty = newQty + 1;
                            else newQty = Math.max(1, newQty - 1);

                            // enviar al backend
                            var tokenEl = document.querySelector('meta[name="csrf-token"]');
                            var token = tokenEl ? tokenEl.getAttribute('content') : '';
                            fetch('{{ route('shopping_carts.update') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ product_id: key, quantity: newQty }),
                                credentials: 'same-origin'
                            }).then(r => r.json()).then(json => {
                                if (json && json.success) {
                                    input.value = newQty;
                                    var row = input.closest('.list-group-item');
                                    if(row){
                                        var price = parseFloat(row.querySelector('.text-muted strong').textContent.replace(/[^0-9\.]/g,'')) || 0;
                                        var subtotalEl = row.querySelector('.fw-bold');
                                        if(subtotalEl){ subtotalEl.textContent = '$' + (price * newQty).toFixed(2); }
                                    }
                                    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: json.count, total: json.total } }));
                                } else if (json && json.allowed_max !== undefined) {
                                    // el backend indicó un max permitido
                                    var allowed = json.allowed_max;
                                    input.value = allowed;
                                    if (typeof showStockModal === 'function') showStockModal('Stock insuficiente. La cantidad máxima disponible es ' + allowed + '.', 'Stock insuficiente'); else alert('Stock insuficiente. La cantidad máxima disponible es ' + allowed + '.');
                                    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: json.count || 0, total: json.total || 0 } }));
                                } else if (json && json.success === false && json.message) {
                                    if (typeof showStockModal === 'function') showStockModal(json.message || 'Operación no permitida', 'Atención'); else alert(json.message || 'Operación no permitida');
                                }
                            }).catch(console.error);
                        }
                        if(e.target.matches('.btn-remove')){
                            var key = e.target.dataset.key;
                            var row = e.target.closest('.list-group-item');
                            var tokenEl = document.querySelector('meta[name="csrf-token"]');
                            var token = tokenEl ? tokenEl.getAttribute('content') : '';
                            fetch('{{ route('shopping_carts.remove') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': token,
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ product_id: key }),
                                credentials: 'same-origin'
                            }).then(r => r.json()).then(json => {
                                if (json && json.success) {
                                    if(row) row.remove();
                                    window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: json.count, total: json.total } }));
                                }
                            }).catch(console.error);
                        }
                });

                // prevent submit on qty input
                document.addEventListener('change', function(e){
                    if(e.target.matches('.qty-input')){
                        var v = parseInt(e.target.value) || 1; e.target.value = Math.max(1, v);
                        recalcAndDispatch();
                    }
                });
            })();
        </script>
        <script>
            // Redirigir al checkout (usa la fachada en el servidor cuando se cargue la ruta)
            (function(){
                var checkoutBtn = document.getElementById('checkoutBtn');
                if(!checkoutBtn) return;
                checkoutBtn.addEventListener('click', function(e){
                    // si está deshabilitado, no hacer nada
                    if(checkoutBtn.disabled) return;
                    window.location.href = '{{ route('checkout.index') }}';
                });
            })();
        </script>
        
                <!-- Modal de stock (reutilizable) -->
                <div class="modal fade" id="stockModal" tabindex="-1" aria-labelledby="stockModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="stockModalLabel">Atención</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body" id="stockModalBody">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                        function showStockModal(message, title) {
                                var label = document.getElementById('stockModalLabel');
                                var body = document.getElementById('stockModalBody');
                                if (label && title) label.textContent = title;
                                if (body) body.textContent = message;
                                try {
                                        var modalEl = document.getElementById('stockModal');
                                        var modal = new bootstrap.Modal(modalEl);
                                        modal.show();
                                } catch (err) {
                                        alert(message);
                                }
                        }
                </script>
    @endsection
</x-app-layout>