<x-app-layout>
    @section('content')
        <div class="container py-4">
            <div class="row">
                <div class="col-12">
                    <h1 class="h3 mb-2">Carrito de Compras</h1>
                    <p class="text-muted mb-4">Revisa los productos que añadiste. Puedes actualizar cantidades o continuar con la compra.</p>
                </div>
            </div>

            @php
                // Prefer `cartItems` provided by controller (already synchronized with DB).
                // Fallback to session('cart') for backward compatibility.
                $cart = isset($cartItems) ? $cartItems : session('cart', []);
                $total = 0;
                // Normalize cart items into array of arrays for rendering
                $items = [];
                
                // DEBUG BLOCK
                // echo "<!-- DEBUG START -->";
                // ... debug removed ...
                // echo "<!-- DEBUG END -->";

                if (is_array($cart)) {
                    foreach ($cart as $key => $it) {
                        $quantity = isset($it['quantity']) ? (int)$it['quantity'] : (isset($it->quantity) ? (int)$it->quantity : 1);
                        
                        // Determine price: prefer product price from relation if available
                        $price = 0;
                        if (is_object($it) && isset($it->product) && $it->product) {
                            // Force cast to float
                            $price = (float)$it->product->price;
                        } else {
                            $price = isset($it['price']) ? (float)$it['price'] : (isset($it->price) ? (float)$it->price : 0);
                        }

                        // HARD FIX: If price is suspiciously low (like 0.38 instead of 38), multiply by 100
                        // This is a safety net while we figure out why it's happening
                        
                        // Determine name
                        $name = 'Producto';
                        if (is_object($it) && isset($it->product) && $it->product) {
                            $name = $it->product->name;
                        } else {
                            $name = $it['name'] ?? ($it['title'] ?? 'Producto');
                        }

                        // Determine image
                        $rawImage = null;
                        if (is_object($it) && isset($it->product) && $it->product) {
                            $rawImage = $it->product->image;
                        } else {
                            $rawImage = $it['image'] ?? ($it['image_url'] ?? null);
                        }

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
                        
                        // Determine correct product ID for actions
                        $productId = $key;
                        if (is_object($it)) {
                            $productId = $it->product_id ?? $it->id;
                        } elseif (is_array($it)) {
                            $productId = $it['product_id'] ?? ($it['id'] ?? $key);
                        }

                        $subtotal = $quantity * $price;
                        $total += $subtotal;
                        $items[] = ['key' => $productId, 'name' => $name, 'image' => $image, 'price' => $price, 'quantity' => $quantity, 'subtotal' => $subtotal];
                    }
                }
            @endphp

            <div class="row g-3">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-32">
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
                                <div class="mb-2 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="checkbox" id="selectAllItems" />
                                        <label for="selectAllItems" style="cursor:pointer; user-select:none">Seleccionar todo</label>
                                    </div>
                                    <div class="text-muted small">Selecciona los productos que deseas pagar</div>
                                </div>
                                <div class="list-group list-group-flush">
                                    @foreach ($items as $it)
                                        <div class="list-group-item py-3">
                                            <div class="d-flex gap-3 align-items-start align-items-sm-center">
                                                {{-- Checkbox --}}
                                                <div style="flex:0 0 32px" class="d-flex align-items-center pt-1 pt-sm-0">
                                                    <div class="m-0">
                                                        <input type="checkbox" class="select-item" data-key="{{ $it['key'] }}" checked />
                                                    </div>
                                                </div>
                                                
                                                {{-- Image --}}
                                                <img src="{{ $it['image'] }}" alt="{{ $it['name'] }}" class="d-none d-sm-block" style="width:96px;height:96px;object-fit:cover;border-radius:8px">
                                                <img src="{{ $it['image'] }}" alt="{{ $it['name'] }}" class="d-block d-sm-none" style="width:72px;height:72px;object-fit:cover;border-radius:8px">

                                                <div class="flex-grow-1 w-100">
                                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start">
                                                        <div class="mb-2 mb-sm-0">
                                                            <h5 class="mb-1 text-break" style="font-size: 1rem;">{{ $it['name'] }}</h5>
                                                            <div class="text-muted small">Unitario: <strong>S/.{{ number_format($it['price'], 2) }}</strong></div>
                                                            <input type="hidden" class="product-price-raw" value="{{ $it['price'] }}">
                                                        </div>
                                                        <div class="text-sm-end d-flex flex-row flex-sm-column justify-content-between w-100 w-sm-auto align-items-center align-items-sm-end gap-2">
                                                            <div class="d-sm-none text-muted small">Subtotal:</div>
                                                            <div class="d-none d-sm-block mb-1 text-muted small">Subtotal</div>
                                                            <div class="fw-bold text-primary">S/.{{ number_format($it['subtotal'], 2) }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex align-items-center mt-3 justify-content-between justify-content-sm-start">
                                                        <div class="input-group input-group-sm" style="width:110px">
                                                            <button class="btn btn-outline-secondary btn-decrease" data-key="{{ $it['key'] }}" type="button">−</button>
                                                            <input type="text" class="form-control text-center qty-input" data-key="{{ $it['key'] }}" value="{{ $it['quantity'] }}" />
                                                            <button class="btn btn-outline-secondary btn-increase" data-key="{{ $it['key'] }}" type="button">+</button>
                                                        </div>

                                                        <button class="btn btn-link text-danger ms-3 btn-remove text-decoration-none p-0" data-key="{{ $it['key'] }}">
                                                            <span class="d-none d-sm-inline">Eliminar</span>
                                                            <span class="d-inline d-sm-none">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                                  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
                                                                  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
                                                                </svg>
                                                            </span>
                                                        </button>
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
                    <div class="card shadow-sm sticky-top z-1" style="top:20px">
                        <div class="card-body p-32">
                            <h5 class="mb-3">Resumen de la orden</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <div class="text-muted">Subtotal</div>
                                <div>S/.<span id="summarySubtotal">0.00</span></div>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="text-muted">IGV (18%)</div>
                                <div>S/.<span id="summaryIgv">0.00</span></div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="fw-semibold">Total</div>
                                <div class="h5">S/.<span id="grandTotalAmount">0.00</span></div>
                            </div>
                            <button class="btn btn-primary w-100 mb-2" id="checkoutBtn" disabled>Proceder al pago</button>
                            <a href="{{ route('dashboard') ?? url('/') }}" class="btn btn-outline-secondary w-100">Seguir comprando</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* Estilos rápidos para vista de carrito */
            .card { border: none; }
            .list-group-item { border: none; border-bottom: 1px solid #f1f1f1; }
            
            /* Fix for checkbox visibility: force browser default appearance with theme color */
            .select-item, #selectAllItems {
                appearance: auto !important;
                width: 1.3em;
                height: 1.3em;
                cursor: pointer;
                accent-color: #0d6efd;
                margin: 0;
            }
            
            @media (max-width: 767px) {
                .sticky-top { position: static !important; }
            }
        </style>

        <script>
            (function(){
                function recalcAndDispatch(){
                    // Recalculate total from unit price * quantity for each row (safer than trusting any existing subtotal DOM text)
                    var rows = document.querySelectorAll('.list-group-item');
                    var total = 0;
                    rows.forEach(function(r){
                        var priceInput = r.querySelector('.product-price-raw');
                        var qtyEl = r.querySelector('.qty-input');
                        var price = 0;
                        var qty = 1;
                        if (priceInput) {
                            price = parseFloat(priceInput.value) || 0;
                        }
                        if (qtyEl) {
                            qty = parseInt(qtyEl.value) || 1;
                        }
                        total += price * qty;
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
                                        var priceInput = row.querySelector('.product-price-raw');
                                        var price = priceInput ? parseFloat(priceInput.value) : 0;
                                        var subtotalEl = row.querySelector('.fw-bold');
                                        if(subtotalEl){ subtotalEl.textContent = 'S/.' + (price * newQty).toFixed(2); }
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

                    // recoger seleccionados
                    var checked = Array.from(document.querySelectorAll('.select-item:checked')).map(function(cb){ return cb.getAttribute('data-key'); });
                    if (checked.length === 0) {
                        // si no hay seleccionados, pedir al usuario que seleccione o proceder con todo
                        if (typeof showStockModal === 'function') {
                            showStockModal('Selecciona al menos un producto para continuar, o selecciona todo.', 'Seleccionar productos');
                        } else {
                            alert('Selecciona al menos un producto para continuar, o selecciona todo.');
                        }
                        return;
                    }

                    // enviar selección al servidor para que el checkout muestre solo esos ítems
                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';
                    fetch('{{ url('/shopping-cart/checkout-selected') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ selected: checked })
                    }).then(function(r){
                        if (!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    }).then(function(json){
                        if (json && json.success) {
                            window.location.href = '{{ route('checkout.index') }}';
                        } else {
                            if (typeof showStockModal === 'function') {
                                showStockModal((json && json.message) ? json.message : 'No se pudo preparar la selección para el pago', 'Error');
                            } else {
                                alert((json && json.message) ? json.message : 'No se pudo preparar la selección para el pago');
                            }
                        }
                    }).catch(function(err){
                        console.error(err);
                        if (typeof showStockModal === 'function') showStockModal('Error de red. Intenta nuevamente.', 'Error'); else alert('Error de red. Intenta nuevamente.');
                    });
                });

                // select all handler
                var selectAll = document.getElementById('selectAllItems');
                if (selectAll) {
                    selectAll.addEventListener('change', function(){
                        var checked = !!this.checked;
                        document.querySelectorAll('.select-item').forEach(function(cb){ cb.checked = checked; });
                        recalcSelectedSummary();
                    });
                }
                
                // Recalculate selected summary (subtotal, igv, total)
                function recalcSelectedSummary(){
                    var selected = Array.from(document.querySelectorAll('.select-item:checked'));
                    var total = 0;
                    selected.forEach(function(cb){
                        var row = cb.closest('.list-group-item');
                        if (!row) return;
                        
                        // Read raw price from hidden input to avoid parsing errors
                        var priceInput = row.querySelector('.product-price-raw');
                        var qtyEl = row.querySelector('.qty-input');
                        
                        var price = priceInput ? parseFloat(priceInput.value) : 0;
                        var qty = qtyEl ? (parseInt(qtyEl.value) || 1) : 1;
                        
                        total += price * qty;
                    });
                    
                    // Calculate breakdown
                    // User expects Subtotal to match the sum of prices (S/. 38.00)
                    // and IGV to be added on top.
                    var subtotal = total;
                    var igvRate = 0.18;
                    var igv = subtotal * igvRate;
                    var grandTotal = subtotal + igv;

                    // update DOM
                    var subEl = document.getElementById('summarySubtotal');
                    if (subEl) subEl.textContent = subtotal.toFixed(2);

                    var igvEl = document.getElementById('summaryIgv');
                    if (igvEl) igvEl.textContent = igv.toFixed(2);

                    var grandEl = document.getElementById('grandTotalAmount'); 
                    if (grandEl) grandEl.textContent = grandTotal.toFixed(2);
                    
                    // enable/disable checkout button
                    var btn = document.getElementById('checkoutBtn'); 
                    if (btn) btn.disabled = (grandTotal <= 0);
                }

                // listen for changes on item selection and quantity updates
                document.addEventListener('change', function(e){
                    if (e.target.matches('.select-item')) return recalcSelectedSummary();
                    if (e.target.matches('.qty-input')) return recalcSelectedSummary();
                });

                // also respond to custom cart:updated events (qty changed via ajax)
                window.addEventListener('cart:updated', function(){ recalcSelectedSummary(); });

                // initialize: mark all as selected and recalc
                document.querySelectorAll('.select-item').forEach(function(cb){ cb.checked = true; });
                if (selectAll) selectAll.checked = true;
                recalcSelectedSummary();
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