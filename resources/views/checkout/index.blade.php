<x-app-layout>
    @section('content')

    <div class="container py-5">
        <div class="row">
            <div class="col-md-8">
                <form id="checkoutForm" action="{{ route('checkout.pay') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h4 class="mb-4">Finalizar compra</h4>

                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input name="customer_name" class="form-control" placeholder="Nombre y apellidos" required />
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de comprobante</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="document_type" id="doc_boleta" value="boleta" checked>
                                    <label class="form-check-label" for="doc_boleta">Boleta</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="document_type" id="doc_factura" value="factura">
                                    <label class="form-check-label" for="doc_factura">Factura</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono (opcional)</label>
                            <input id="phoneInput" name="phone" class="form-control" placeholder="9XXXXXXXX" inputmode="numeric" />
                        </div>
                    </div>

                    <div id="dniGroup" class="mb-3">
                        <label class="form-label">DNI</label>
                        <input id="dniInput" name="dni" class="form-control" placeholder="DNI (8 dígitos)" inputmode="numeric" />
                    </div>
                    <div id="rucGroup" class="mb-3 d-none">
                        <label class="form-label">RUC</label>
                        <input id="rucInput" name="ruc" class="form-control" placeholder="RUC (11 dígitos)" inputmode="numeric" />
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

                    <div id="yapeFields" style="display:none;">
                        <div class="mb-2">
                            <label class="form-label">QR (Yape)</label>
                            <div class="d-flex">
                                <img src="/img/QR_YAPE.jpg" alt="Yape QR Code" class="img-fluid my-3" style="max-width: 200px;" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subir comprobante (imagen o PDF)</label>
                            <input type="file" name="yape_receipt" id="yapeReceiptInput" accept="image/*,application/pdf" class="form-control" />
                            <div class="form-text">Sube la imagen o PDF del comprobante de pago para que podamos validar tu pago manualmente.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">Pagar</button>
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
                        var receiptInput = document.getElementById('yapeReceiptInput');
                        var payButton = form ? form.querySelector('button[type="submit"]') : null;
                        var radios = form ? form.querySelectorAll('input[name="payment_method"]') : [];
                        function update() {
                            var v = form ? (form.querySelector('input[name="payment_method"]:checked') || {}).value : null;
                            if (v === 'yape') {
                                if(cardFields) cardFields.style.display = 'none';
                                if(yapeFields) yapeFields.style.display = 'block';
                                if (receiptInput) receiptInput.required = true;
                                if (payButton) payButton.textContent = 'Subir y guardar';
                            } else {
                                if(cardFields) cardFields.style.display = 'block';
                                if(yapeFields) yapeFields.style.display = 'none';
                                if (receiptInput) receiptInput.required = false;
                                if (payButton) payButton.textContent = 'Pagar';
                            }
                        }
                        Array.prototype.forEach.call(radios, function(r){ r.addEventListener('change', update); });
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
                            var sel = document.querySelector('input[name="document_type"]:checked');
                            var val = sel ? sel.value : null;
                            if (val === 'boleta'){
                                if(dniGroup) dniGroup.classList.remove('d-none');
                                if(rucGroup) rucGroup.classList.add('d-none');
                                if(dniInput) dniInput.required = true;
                                if(rucInput) rucInput.required = false;
                            } else {
                                if(dniGroup) dniGroup.classList.add('d-none');
                                if(rucGroup) rucGroup.classList.remove('d-none');
                                if(dniInput) dniInput.required = false;
                                if(rucInput) rucInput.required = true;
                            }
                        }
                        Array.prototype.forEach.call(docRadios, function(r){ r.addEventListener('change', updateDoc); });
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
                                    var message = (data && data.message) ? data.message : 'No se pudo procesar la compra.';
                                    // If backend provided details about stock, show a clearer message
                                    if (data && data.details && data.details.product_name) {
                                        var d = data.details;
                                        message = 'Stock insuficiente para "' + d.product_name + '". Solicitado: ' + (d.requested || 'N/A') + '. Disponibles: ' + (d.available || 0) + '.';
                                    }
                                    if (processingText) processingText.textContent = 'Error: ' + message;
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

            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="text-muted">Resumen</h6>
                    <ul class="list-unstyled mt-3 mb-3" style="max-height: 260px; overflow:auto">
                        @foreach($items as $it)
                            <li class="d-flex justify-content-between align-items-center mb-2">
                                <div class="text-truncate" style="max-width:160px;">{{ $it->name ?? ($it['name'] ?? 'Producto') }}</div>
                                <div class="text-muted">{{ $it->quantity ?? ($it['qty'] ?? 1) }}× S/.{{ number_format($it->price ?? ($it['unit_price'] ?? 0),2) }}</div>
                            </li>
                        @endforeach
                    </ul>
                    <hr>
                    <div class="d-flex justify-content-between"><div class="text-muted">Subtotal</div><div>S/.{{ number_format($subtotal ?? 0,2) }}</div></div>
                    <div class="d-flex justify-content-between"><div class="text-muted">IGV (18%)</div><div>S/.{{ number_format($igv ?? 0,2) }}</div></div>
                    <div class="d-flex justify-content-between mt-2"><div class="fw-semibold">Total</div><div class="fw-bold">S/.{{ number_format($total ?? 0,2) }}</div></div>
                </div>
            </div>
        </div>
    </div>

    @endsection
</x-app-layout>