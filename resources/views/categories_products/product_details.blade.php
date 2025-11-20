<x-app-layout>

    <div class="container py-5" style="background: #f3f3f3">
        <div class="product-details" style="width:100%;max-width:1100px;margin:0 auto; box-shadow:0 6px 20px rgba(0,0,0,0.06);">
            <div class="product-grid" style="display:flex;flex-wrap:wrap;">
                {{-- Left: gallery / image --}}
                <div class="product-gallery" style="flex:1 1 420px;min-width:280px;">
                    @if($product->image)
                        <div style="border-radius:8px 0 0 8px;background:#fff;">
                            <img id="mainProductImage" src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:100%;display:block;object-fit:cover;">
                        </div>
                    @else
                        <div style="height:320px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;border-radius:8px;color:#6b7280;">Sin imagen</div>
                    @endif
                    {{-- Thumbnail row could go here in future --}}
                </div>

                {{-- Right: product info --}}
                <div class="product-info" style="flex:1 1 360px;min-width:280px; background:#fff; padding:20px; border-radius:0 8px 8px 0; box-shadow:0 6px 18px rgba(0,0,0,0.04);">
                    <h1 style="font-size:1.6rem;margin-bottom:8px;color:#0b1220;">{{ $product->name }}</h1>

                    {{-- Rating and stock/info line --}}
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;justify-content:space-between;">
                        <div style="display:flex;align-items:center;gap:8px;color:#f59e0b;">
                            @for ($i = 0; $i < 5; $i++)
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 .587l3.668 7.431L23.4 9.75l-5.7 5.556L19.336 24 12 20.201 4.664 24l1.636-8.694L.6 9.75l7.732-1.732L12 .587z"/></svg>
                            @endfor
                            <div style="color:#6b7280;font-size:0.95rem;margin-left:8px;">Disponible: <strong style="color:#064e3b;">@if($product->stock ?? false){{ $product->stock }}@else{{ '---' }}@endif</strong></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:1.45rem;font-weight:700;color:#064e3b;">S/.{{ number_format($product->price, 2) }}</div>
                            @if($product->old_price ?? false)
                                <div style="text-decoration:line-through;color:#6b7280;font-size:0.95rem;">S/.{{ number_format($product->old_price,2) }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Quantity + actions --}}
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
                        <div style="display:flex;align-items:center;border:1px solid #e6e7eb;border-radius:8px;overflow:hidden;">
                            <button id="qtyMinus" type="button" style="background:#fff;border:0;padding:10px 12px;cursor:pointer;color:#374151;">-</button>
                            <input id="quantity" type="text" value="1" readonly style="width:56px;text-align:center;border-left:1px solid #eee;border-right:1px solid #eee;padding:8px 6px;font-weight:600;">
                            <button id="qtyPlus" type="button" style="background:#fff;border:0;padding:10px 12px;cursor:pointer;color:#374151;">+</button>
                        </div>

                        <div style="display:flex;gap:8px;flex-wrap:nowrap;">
                            <form id="addToCartForm" method="POST" action="{{ route('shopping_carts.add') }}" data-stock="{{ $product->stock ?? '' }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" id="formQuantity" value="1">
                                <button id="addToCartBtn" type="submit" class="btn btn-success" style="background:#10b981;border-color:#10b981;padding:10px 18px;font-weight:700;">Agregar al carrito</button>
                                <button id="buyNowBtn" name="buy_now" value="1" type="button" class="btn btn-outline-primary" style="margin-left:8px;padding:10px 14px;border-radius:8px;border:1px solid #c7d2fe;color:#374151;background:#fff;">Comprar ahora</button>
                            </form>
                        </div>
                    </div>

                    {{-- Short attributes / SKU --}}
                    <ul style="color:#475569;font-size:0.95rem;margin-bottom:12px;line-height:1.6;">
                        @if($product->sku)
                            <li><strong>SKU:</strong> {{ $product->sku }}</li>
                        @endif
                        @if($product->category)
                            <li><strong>Categoría:</strong> {{ $product->category->name }}</li>
                        @endif
                    </ul>

                    {{-- Full description --}}
                    <div style="margin-top:8px;background:#fff;padding:14px;border-radius:8px;">
                        <h3 style="margin-bottom:8px;color:#0b1220;">Descripción</h3>
                        <div style="color:#374151;line-height:1.6;">{!! nl2br(e($product->description)) !!}</div>
                    </div>

                    {{-- Social / share (simple) --}}
                    <div style="display:flex;gap:8px;align-items:center;color:#6b7280;font-size:0.95rem;margin-top:12px;">
                        <span>Compartir:</span>
                        <a href="#" style="color:#2563eb;">Facebook</a>
                        <a href="#" style="color:#0ea5e9;">Twitter</a>
                    </div>
                </div>
            </div>

        </div>

        {{-- Related products: otros productos de la misma categoría --}}
        @php
            $related = collect();
            if($product->category) {
                // obtener hasta 8 productos de la misma categoría excluyendo el actual
                $related = $product->category->products()->where('id','<>',$product->id)->where('status',1)->take(8)->get();
            }
        @endphp

        @if($related->count())
            <div style="max-width:1100px;margin:28px auto 0;padding:0 16px;">
                {{-- Category badge above related products --}}
                <div style="margin-bottom:8px;">
                    <span style="display:inline-block;background:#eef2ff;color:#3730a3;padding:6px 10px;border-radius:999px;font-weight:600;font-size:0.95rem;">{{ $product->category->name }}</span>
                </div>
                <h3 style="font-size:1.15rem;margin-bottom:12px;color:#0b1220;">Otros productos</h3>
                <div style="display:flex;flex-wrap:wrap;gap:16px;">
                    @foreach($related as $r)
                        <div style="width:calc(25% - 12px);min-width:180px;background:#fff;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.03);overflow:hidden;">
                            <a href="{{ route('products.details', $r->id) }}" style="display:block;text-decoration:none;color:inherit;">
                                @if($r->image)
                                    <div style="height:140px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:#ffffff;padding:6px;">
                                        <img src="{{ asset('storage/' . $r->image) }}" alt="{{ $r->name }}" style="width:100%;height:140px;object-fit:contain;display:block;">
                                    </div>
                                @else
                                    <div style="height:140px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#6b7280;">Sin imagen</div>
                                @endif
                                <div style="padding:10px;">
                                    <div style="font-weight:600;font-size:0.95rem;margin-bottom:6px;">{{ Str::limit($r->name, 40) }}</div>
                                    <div style="color:#064e3b;font-weight:700;">S/.{{ number_format($r->price,2) }}</div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- Small inline script to handle quantity selector --}}
    <script>
        (function(){
            const qtyInput = document.getElementById('quantity');
            const formQty = document.getElementById('formQuantity');
            const plus = document.getElementById('qtyPlus');
            const minus = document.getElementById('qtyMinus');
            function setQty(val){
                const n = Math.max(1, parseInt(val)||1);
                qtyInput.value = n;
                if(formQty) formQty.value = n;
            }
            if(plus) plus.addEventListener('click', ()=> setQty((parseInt(qtyInput.value)||1)+1));
            if(minus) minus.addEventListener('click', ()=> setQty((parseInt(qtyInput.value)||1)-1));
            
            // Interceptar el submit del formulario y enviar via fetch para actualizar badge sin recargar
            const addForm = document.getElementById('addToCartForm');
            const addBtn = document.getElementById('addToCartBtn');
            const stockAvailable = addForm ? (addForm.dataset.stock ? parseInt(addForm.dataset.stock) : null) : null;
                    if (addForm) {
                addForm.addEventListener('submit', function(e){
                    e.preventDefault();
                    // Deshabilitar botón temporalmente
                    const originalText = (addBtn && addBtn.textContent) ? addBtn.textContent.trim() : '';
                    if(addBtn){ addBtn.disabled = true; addBtn.textContent = 'Agregando...'; }

                    const fd = new FormData(addForm);
                    // validar contra stock antes de hacer la petición
                    if (stockAvailable !== null) {
                        const q = parseInt(fd.get('quantity') || 0);
                        if (q > stockAvailable) {
                            // mostrar modal de stock
                            if (typeof showStockModal === 'function') {
                                showStockModal('No hay suficiente stock. Solo quedan ' + stockAvailable + ' unidades.', 'Stock insuficiente');
                            } else {
                                alert('No hay suficiente stock. Solo quedan ' + stockAvailable + ' unidades.');
                            }
                            addBtn.innerHTML = originalText;
                            addBtn.disabled = false;
                            return;
                        }
                    }

                    // NOTE: buyNow handler moved outside submit handler (registered below)

                    fetch(addForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                        credentials: 'same-origin'
                    }).then(resp => resp.json()).then(json => {
                        if (json && json.success) {
                            // Actualizar badge: si existe helper window.updateCartBadge, usarlo
                            if (window.updateCartBadge && typeof window.updateCartBadge === 'function') {
                                window.updateCartBadge(json.count);
                            } else {
                                // Dispatch event para que quien escuche actualice el badge
                                const ev = new CustomEvent('cart:updated', { detail: { count: json.count, total: json.total } });
                                window.dispatchEvent(ev);
                            }
                            // Feedback breve
                            if(addBtn){
                                addBtn.textContent = 'Agregado';
                                addBtn.classList.remove('btn-success');
                                addBtn.classList.add('btn-secondary');
                            }
                            // mantener feedback visible un poco más tiempo
                            setTimeout(()=>{ if(addBtn){ addBtn.textContent = originalText; addBtn.disabled = false; addBtn.classList.remove('btn-secondary'); addBtn.classList.add('btn-success'); } }, 1400);
                        } else {
                            // si backend responde con error de stock, ajustar cantidad visible
                            if (json && json.allowed_add !== undefined) {
                                var msg = 'Stock insuficiente. Solo se han agregado ' + json.allowed_add + ' unidades adicionales.';
                                if (typeof showStockModal === 'function') showStockModal(msg, 'Stock insuficiente'); else alert(msg);
                            } else if (json && json.message) {
                                if (typeof showStockModal === 'function') showStockModal(json.message, 'Atención'); else alert(json.message);
                            } else {
                                alert('No se pudo agregar el producto al carrito.');
                            }
                            if(addBtn){ addBtn.textContent = originalText; addBtn.disabled = false; }
                        }
                    }).catch(err => {
                        console.error(err);
                        if(addBtn){ addBtn.textContent = originalText; addBtn.disabled = false; }
                        alert('Error de red al agregar al carrito.');
                    });
                });
            }

            // Register 'Comprar ahora' handler once (outside submit flow) so it doesn't interfere
            // with the add-to-cart submit handler.
            (function(){
                const buyNowBtn = document.getElementById('buyNowBtn');
                if (!buyNowBtn || !addForm) return;

                    buyNowBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    const fd = new FormData(addForm);
                        // Indicate this is a 'buy now' intent so backend can validate accordingly
                        fd.append('buy_now', '1');
                    // validate stock
                    if (stockAvailable !== null) {
                        const q = parseInt(fd.get('quantity') || 0);
                        if (q > stockAvailable) {
                            if (typeof showStockModal === 'function') showStockModal('No hay suficiente stock. Solo quedan ' + stockAvailable + ' unidades.', 'Stock insuficiente'); else alert('No hay suficiente stock. Solo quedan ' + stockAvailable + ' unidades.');
                            return;
                        }
                    }

                    const originalBuyText = buyNowBtn.textContent.trim();
                    buyNowBtn.textContent = 'Procesando...';
                    buyNowBtn.disabled = true;

                    fetch(addForm.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd,
                        credentials: 'same-origin'
                    }).then(r => r.json()).then(json => {
                        if (json && json.success) {
                            if (window.updateCartBadge && typeof window.updateCartBadge === 'function') {
                                window.updateCartBadge(json.count);
                            } else {
                                window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count: json.count, total: json.total } }));
                            }
                            // redirect to checkout
                            window.location.href = '{{ route('checkout.index') }}';
                        } else {
                            if (json && json.allowed_add !== undefined) {
                                var msg = 'Stock insuficiente. Solo se han agregado ' + json.allowed_add + ' unidades adicionales.';
                                if (typeof showStockModal === 'function') showStockModal(msg, 'Stock insuficiente'); else alert(msg);
                            } else if (json && json.message) {
                                if (typeof showStockModal === 'function') showStockModal(json.message, 'Atención'); else alert(json.message);
                            } else {
                                alert('No se pudo proceder al pago.');
                            }
                            buyNowBtn.textContent = originalBuyText;
                            buyNowBtn.disabled = false;
                        }
                    }).catch(err => {
                        console.error(err);
                        buyNowBtn.textContent = originalBuyText;
                        buyNowBtn.disabled = false;
                        alert('Error de red al intentar procesar la compra.');
                    });
                });
            })();
        })();
    </script>

        <!-- Modal de stock -->
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
                // helper para mostrar modal de stock si Bootstrap está disponible
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

</x-app-layout>
