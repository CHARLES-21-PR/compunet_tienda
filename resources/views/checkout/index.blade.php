<x-app-layout>
    @section('content')
        <div class="container py-4">
            <h2>Checkout</h2>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Resumen</h5>
                    @if(!empty($items))
                        <ul class="list-unstyled">
                            @foreach($items as $it)
                                <li class="mb-2">{{ $it['product']->name }} — Cant: {{ $it['quantity'] }} — S/.{{ number_format($it['price'],2) }}</li>
                            @endforeach
                        </ul>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between"><div class="text-muted">Subtotal</div><div>S/.{{ number_format($subtotal,2) }}</div></div>
                            <div class="d-flex justify-content-between"><div class="text-muted">IGV (18%)</div><div>S/.{{ number_format($igv,2) }}</div></div>
                            <hr>
                            <div class="d-flex justify-content-between"><div class="fw-semibold">Total</div><div class="fw-bold">S/.{{ number_format($total,2) }}</div></div>
                        </div>
                    @else
                        <p>El carrito está vacío.</p>
                    @endif
                </div>
            </div>

            <form method="POST" action="{{ route('checkout.pay') ?? url('/checkout/pay') }}" id="checkoutForm">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nombre del cliente</label>
                    <input name="customer_name" class="form-control form-control-lg rounded" placeholder="Nombre completo" required autocomplete="name" />
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo de comprobante</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_type" id="doc_boleta" value="boleta" checked>
                            <label class="form-check-label" for="doc_boleta">Boleta (DNI)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="document_type" id="doc_factura" value="factura">
                            <label class="form-check-label" for="doc_factura">Factura (RUC)</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-2" id="dniGroup">
                        <label class="form-label">DNI (8 dígitos)</label>
                        <input name="dni" id="dniInput" class="form-control form-control-lg rounded" inputmode="numeric" pattern="\d{8}" maxlength="8" placeholder="12345678" />
                    </div>
                    <div class="col-md-6 mb-2 d-none" id="rucGroup">
                        <label class="form-label">RUC (11 dígitos)</label>
                        <input name="ruc" id="rucInput" class="form-control form-control-lg rounded" inputmode="numeric" pattern="\d{11}" maxlength="11" placeholder="20123456789" />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <input name="address" class="form-control" placeholder="Dirección de envío (opcional)" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Método de pago</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_bcp" value="bcp_card" checked>
                            <label class="form-check-label" for="pm_bcp">Tarjeta BCP</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_interbank" value="interbank_card">
                            <label class="form-check-label" for="pm_interbank">Tarjeta Interbank</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_bbva" value="bbva_card">
                            <label class="form-check-label" for="pm_bbva">Tarjeta BBVA</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="payment_method" id="pm_yape" value="yape">
                            <label class="form-check-label" for="pm_yape">Yape</label>
                        </div>
                    </div>
                </div>

                <div id="cardFields">
                    <div class="mb-2">
                        <label class="form-label">Número de tarjeta (simulado)</label>
                        <input name="card_number" class="form-control form-control-lg rounded" maxlength="19" placeholder="4111 1111 1111 1111" inputmode="numeric" />
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Nombre en tarjeta</label>
                            <input name="card_holder" class="form-control form-control-lg rounded text-uppercase" placeholder="NOMBRE APELLIDO" autocomplete="cc-name" />
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Expira (MM/AA)</label>
                            <input name="expiry" class="form-control form-control-lg rounded" placeholder="12/25" maxlength="5" inputmode="numeric" />
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">CVC</label>
                            <input name="cvc" class="form-control form-control-lg rounded" placeholder="123" maxlength="4" inputmode="numeric" />
                        </div>
                    </div>
                </div>

                <div id="yapeFields" style="display:none">
                    <div class="mb-2">
                        <label class="form-label">Número de teléfono (Yape)</label>
                        <input name="phone" id="phoneInput" class="form-control form-control-lg rounded" placeholder="9XXXXXXXX" maxlength="9" inputmode="numeric" />
                    </div>
                </div>

                <button class="btn btn-primary btn-lg w-100">Pagar</button>
            </form>

            <!-- Modal: Procesando pago -->
            <div class="modal fade" id="processingModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center" id="processingModalBody">
                            <div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Loading...</span></div>
                            <div id="processingText" class="fw-semibold">Verificando la compra, por favor espera...</div>
                        </div>
                        <div class="modal-footer" id="processingModalFooter" style="display:none">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <a href="#" id="processingActionBtn" class="btn btn-primary">Continuar</a>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function(){
                    var form = document.getElementById('checkoutForm');
                    var cardFields = document.getElementById('cardFields');
                    var yapeFields = document.getElementById('yapeFields');
                    var radios = form.querySelectorAll('input[name="payment_method"]');
                    function update() {
                        var v = form.querySelector('input[name="payment_method"]:checked').value;
                        if (v === 'yape') { cardFields.style.display = 'none'; yapeFields.style.display = 'block'; }
                        else { cardFields.style.display = 'block'; yapeFields.style.display = 'none'; }
                    }
                    radios.forEach(function(r){ r.addEventListener('change', update); });
                    update();
                })();
                // formateo automático de número de tarjeta (grupos de 4)
                (function(){
                    var cardInput = document.querySelector('input[name="card_number"]');
                    if(!cardInput) return;
                    function formatCard(value){
                        var digits = value.replace(/\D/g, '').slice(0,16); // limitar a 16 dígitos
                        return digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
                    }
                    cardInput.addEventListener('input', function(e){
                        var pos = this.selectionStart;
                        var old = this.value;
                        this.value = formatCard(this.value);
                        // intentar mantener caret al final simple
                        if (pos >= old.length) {
                            this.selectionStart = this.selectionEnd = this.value.length;
                        }
                    });
                    // optional: format on blur to ensure spacing
                    cardInput.addEventListener('blur', function(){ this.value = formatCard(this.value); });
                })();

                // mostrar/ocultar inputs DNI/RUC según tipo de comprobante
                (function(){
                    var docRadios = document.querySelectorAll('input[name="document_type"]');
                    var dniGroup = document.getElementById('dniGroup');
                    var rucGroup = document.getElementById('rucGroup');
                    var dniInput = document.getElementById('dniInput');
                    var rucInput = document.getElementById('rucInput');
                    function updateDoc(){
                        var val = document.querySelector('input[name="document_type"]:checked').value;
                        if (val === 'boleta'){
                            dniGroup.classList.remove('d-none');
                            rucGroup.classList.add('d-none');
                            if(dniInput) dniInput.required = true;
                            if(rucInput) rucInput.required = false;
                        } else {
                            dniGroup.classList.add('d-none');
                            rucGroup.classList.remove('d-none');
                            if(dniInput) dniInput.required = false;
                            if(rucInput) rucInput.required = true;
                        }
                    }
                    docRadios.forEach(function(r){ r.addEventListener('change', updateDoc); });
                    updateDoc();
                })();

                // Restricciones: permitir solo dígitos en ciertos campos y limitar longitud
                (function(){
                    function onlyDigitsListener(el, maxLen){
                        if(!el) return;
                        el.addEventListener('input', function(e){
                            var v = this.value.replace(/\D/g,'').slice(0, maxLen || 999);
                            this.value = v;
                        });
                    }
                    onlyDigitsListener(document.getElementById('dniInput'), 8);
                    onlyDigitsListener(document.getElementById('rucInput'), 11);
                    onlyDigitsListener(document.getElementById('phoneInput'), 9);
                    // CVC and expiry numeric filtering
                    var cvc = document.querySelector('input[name="cvc"]'); if(cvc) onlyDigitsListener(cvc, 4);
                    var expiry = document.querySelector('input[name="expiry"]'); if(expiry) {
                        expiry.addEventListener('input', function(){
                            var v = this.value.replace(/[^0-9]/g,'').slice(0,4);
                            if(v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
                            this.value = v;
                        });
                    }
                })();

            (function(){
                var form = document.getElementById('checkoutForm');
                if (!form) return;
                function showProcessing(){
                    try { var modalEl = document.getElementById('processingModal'); var m = new bootstrap.Modal(modalEl); m.show(); return m; } catch(e){ alert('Procesando...'); return null; }
                }

                form.addEventListener('submit', function(e){
                    // intercept normal submit and perform AJAX so we can show modal updates
                    e.preventDefault();
                    var m = showProcessing();
                    var processingText = document.getElementById('processingText');
                    var footer = document.getElementById('processingModalFooter');
                    var actionBtn = document.getElementById('processingActionBtn');

                    var fd = new FormData(form);
                    // send via fetch
                    fetch(form.action, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    }).then(function(resp){
                        return resp.json().catch(function(){ return { success: false, message: 'Respuesta inválida del servidor' }; });
                    }).then(function(data){
                        if (data && data.success) {
                            // show success message and redirect after short delay or let user continue
                            if (processingText) processingText.textContent = 'Compra verificada. Redirigiendo...';
                            if (footer) footer.style.display = 'none';
                            setTimeout(function(){ window.location = data.redirect || ('/checkout/success/' + (data.order_id || '')); }, 900);
                        } else {
                            // show error inside modal and allow user to close
                            if (processingText) processingText.textContent = 'Error: ' + (data && data.message ? data.message : 'No se pudo procesar la compra.');
                            if (footer) { footer.style.display = 'flex'; actionBtn.href = '#'; actionBtn.addEventListener('click', function(){ if (m) m.hide(); }); }
                        }
                    }).catch(function(err){
                        if (processingText) processingText.textContent = 'Error de red: ' + (err && err.message ? err.message : '');
                        if (footer) { footer.style.display = 'flex'; actionBtn.href = '#'; actionBtn.addEventListener('click', function(){ if (m) m.hide(); }); }
                    });
                });
            })();
            </script>
        </div>
    @endsection
</x-app-layout>