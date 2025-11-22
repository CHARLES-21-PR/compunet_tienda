<x-app-layout>
    @section('content')

    <div class="container py-5">
        <!-- Bootstrap Icons CDN (fallback si layout no lo incluye) -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <style>
            .stepper { display:flex; gap:12px; align-items:center; }
            .step { flex:1; text-align:center; padding:8px 10px; border-radius:8px; background:#f8fafc; color:#6b7280; font-weight:600 }
            .step.active { background:linear-gradient(90deg,#6366f1,#06b6d4); color:white; }
            .product-thumb { width:48px; height:48px; object-fit:cover; border-radius:6px; margin-right:8px; }
            .pay-method { border:1px solid #e6e7eb; padding:8px 12px; border-radius:8px; cursor:pointer }
            .pay-method input { margin-right:8px }
            .summary-item { display:flex; align-items:center; gap:10px }
        </style>

        <div class="row mb-4">
            <div class="col-12">
                <div class="stepper">
                    <div class="step active">Método de pago</div>
                    <div class="step">Pago</div>
                    <div class="step">Confirmación</div>
                </div>
            </div>
        </div>

        <div class="row gx-4 gy-4">
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <h3 class="mb-0">Pagar pedido</h3>
                                <div class="small text-muted">Completa tus datos para generar el comprobante</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">Seguro</span>
                            </div>
                        </div>

                        <form id="checkoutForm" action="{{ route('checkout.pay') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- STEP 1: Seleccionar método de pago -->
                            <div id="step-1" class="checkout-step">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Nombre completo</label>
                                        <input name="customer_name" class="form-control form-control-lg" placeholder="Nombre y apellidos" required />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Documento</label>
                                        <div class="d-flex gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="document_type" id="doc_boleta" value="boleta" checked>
                                                <label class="form-check-label" for="doc_boleta">Boleta</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="document_type" id="doc_factura" value="factura">
                                                <label class="form-check-label" for="doc_factura">Factura</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Teléfono</label>
                                        <input id="phoneInput" name="phone" class="form-control" placeholder="9XXXXXXXX" inputmode="numeric" />
                                    </div>

                                    <div class="col-md-6" id="dniGroup">
                                        <label class="form-label">DNI</label>
                                        <input id="dniInput" name="dni" class="form-control" placeholder="DNI (8 dígitos)" inputmode="numeric" maxlength="8" />
                                    </div>
                                    <div class="col-md-6 d-none" id="rucGroup">
                                        <label class="form-label">RUC</label>
                                        <input id="rucInput" name="ruc" class="form-control" placeholder="RUC (11 dígitos)" inputmode="numeric" maxlength="11" />
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Dirección</label>
                                        <input name="address" class="form-control" placeholder="Dirección de envío (opcional)" />
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Método de pago</label>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <label class="pay-method d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method" id="pm_bcp" value="bcp_card" checked>
                                                <i class="bi bi-credit-card-2-front-fill me-2" style="font-size:1.15rem"></i> Tarjeta BCP
                                            </label>
                                            <label class="pay-method d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method" id="pm_interbank" value="interbank_card">
                                                <i class="bi bi-credit-card-2-front-fill me-2" style="font-size:1.15rem"></i> Interbank
                                            </label>
                                            <label class="pay-method d-flex align-items-center">
                                                <input class="form-check-input" type="radio" name="payment_method" id="pm_bbva" value="bbva_card">
                                                <i class="bi bi-credit-card-2-front-fill me-2" style="font-size:1.15rem"></i> BBVA
                                            </label>
                                            <label class="pay-method d-flex align-items-center" title="Disponible para pedidos hasta S/.500">
                                                @php $renderTotal = $total ?? ($viewTotal ?? 0); @endphp
                                                <input class="form-check-input" type="radio" name="payment_method" id="pm_yape" value="yape" @if($renderTotal > 500) disabled @endif>
                                                <i class="bi bi-phone me-2" style="font-size:1.15rem"></i> Yape
                                                @if($renderTotal > 500)
                                                    <small class="text-muted ms-2">(Disponible para pedidos hasta S/.500)</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" id="toStep2Btn" class="btn btn-primary">Siguiente</button>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 2: Datos de pago -->
                            <div id="step-2" class="checkout-step" style="display:none;">
                                <div class="row g-3">
                                    <div class="col-12" id="cardFields">
                                        <div class="row g-2">
                                            <div class="col-md-12">
                                                <label class="form-label">Número de tarjeta (simulado)</label>
                                                <input name="card_number" class="form-control form-control-lg" maxlength="19" placeholder="4111 1111 1111 1111" inputmode="numeric" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Nombre en tarjeta</label>
                                                <input name="card_holder" class="form-control text-uppercase" placeholder="NOMBRE APELLIDO" autocomplete="cc-name" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Expira (MM/AA)</label>
                                                <input name="expiry" class="form-control" placeholder="12/25" maxlength="5" inputmode="numeric" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">CVC</label>
                                                <input name="cvc" class="form-control" placeholder="123" maxlength="4" inputmode="numeric" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12" id="yapeFields" style="display:none;">
                                        <div class="mb-2">
                                            <label class="form-label">QR (Yape)</label>
                                            <div class="d-flex">
                                                <img src="/img/QR_YAPE.jpg" alt="Yape QR Code" class="img-fluid" style="max-width: 200px; border-radius:8px;" />
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Subir comprobante (imagen o PDF)</label>
                                            <input type="file" name="yape_receipt" id="yapeReceiptInput" accept="image/*,application/pdf" class="form-control" />
                                            <div class="form-text">Sube la imagen o PDF del comprobante para validación manual.</div>
                                        </div>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button type="button" id="backToStep1Btn" class="btn btn-outline-secondary">Atrás</button>
                                        <button type="button" id="toStep3Btn" class="btn btn-primary">Siguiente</button>
                                    </div>
                                </div>
                            </div>

                            <!-- STEP 3: Confirmación -->
                            <div id="step-3" class="checkout-step" style="display:none;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <h5>Confirma tu pedido</h5>
                                        <div class="small text-muted mb-2">Revisa los datos y confirma para realizar el pago.</div>
                                        <ul class="list-unstyled">
                                            <li><strong>Nombre:</strong> <span id="confName"></span></li>
                                            <li><strong>Documento:</strong> <span id="confDoc"></span></li>
                                            <li><strong>Teléfono:</strong> <span id="confPhone"></span></li>
                                            <li><strong>Método:</strong> <span id="confMethod"></span></li>
                                        </ul>
                                    </div>

                                    <div class="col-12 d-flex justify-content-between">
                                        <button type="button" id="backToStep2Btn" class="btn btn-outline-secondary">Atrás</button>
                                        <button id="submitBtn" type="submit" class="btn btn-primary">
                                            <span id="submitBtnText">Pagar ahora</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Resumen de pedido</h6>
                        <div class="mt-3">
                            @php
                                // Ensure $items is populated; fallback to session('cart') if controller didn't pass items
                                if (empty($items) || !is_iterable($items)) {
                                    $cart = session('cart', []);
                                    $items = [];
                                    if (is_array($cart)) {
                                        foreach ($cart as $key => $it) {
                                            $quantity = isset($it['quantity']) ? (int)$it['quantity'] : (isset($it->quantity) ? (int)$it->quantity : 1);
                                            $price = isset($it['price']) ? (float)$it['price'] : (isset($it->product->price) ? (float)$it->product->price : 0);
                                            $name = $it['name'] ?? ($it['title'] ?? (isset($it->product->name) ? $it->product->name : 'Producto'));
                                            $rawImage = $it['image'] ?? ($it['image_url'] ?? (isset($it->product->image) ? $it->product->image : null));
                                            if ($rawImage) {
                                                // normalize backslashes
                                                $rawImage = str_replace('\\', '/', $rawImage);
                                                // full URL or protocol-relative
                                                if (preg_match('#^(https?:)?//#i', $rawImage) ) {
                                                    $image = $rawImage;
                                                }
                                                // leading slash -> public path '/storage/..' or '/img/...'
                                                else if (strpos($rawImage, '/') === 0) {
                                                    $image = $rawImage;
                                                }
                                                // starts with storage/ -> public storage path
                                                else if (preg_match('#^storage/#i', $rawImage)) {
                                                    $image = asset($rawImage);
                                                }
                                                // starts with img/ or assets/ -> public folder
                                                else if (preg_match('#^(img|assets)/#i', $rawImage)) {
                                                    $image = asset($rawImage);
                                                }
                                                else {
                                                    // fallback: treat as storage path
                                                    $imgClean = preg_replace('#^storage/#i', '', $rawImage);
                                                    $image = asset('storage/' . $imgClean);
                                                }
                                            } else {
                                                $image = asset('img/c1.webp');
                                            }
                                            $items[] = ['name' => $name, 'image' => $image, 'price' => $price, 'quantity' => $quantity, 'subtotal' => $quantity * $price];
                                        }
                                    }
                                }
                            @endphp

                            @foreach($items as $it)
                                <div class="summary-item mb-2">
                                    @php
                                        // determine raw image from various shapes: array with 'image', array with 'product', or object with product
                                        $rawImage = null;
                                        $displayName = 'Producto';
                                        $displayQty = 1;
                                        $displayPrice = 0;
                                        $displaySubtotal = null;

                                        if (is_array($it)) {
                                            if (!empty($it['image'])) $rawImage = $it['image'];
                                            if (!empty($it['product']) && is_object($it['product'])) {
                                                $rawImage = $rawImage ?? ($it['product']->image ?? null);
                                            }
                                            $displayName = $it['name'] ?? ($it['product']->name ?? 'Producto');
                                            $displayQty = $it['quantity'] ?? ($it['qty'] ?? 1);
                                            $displayPrice = $it['price'] ?? ($it['unit_price'] ?? 0);
                                            $displaySubtotal = $it['subtotal'] ?? ($displayQty * $displayPrice);
                                        } else {
                                            // object form (from CheckoutService -> items with 'product' key)
                                            if (!empty($it->product) && is_object($it->product)) {
                                                $rawImage = $it->product->image ?? null;
                                                $displayName = $it->product->name ?? ($it->name ?? 'Producto');
                                            } else {
                                                $rawImage = $it->image ?? null;
                                                $displayName = $it->name ?? 'Producto';
                                            }
                                            $displayQty = $it->quantity ?? 1;
                                            $displayPrice = $it->price ?? 0;
                                            $displaySubtotal = ($displayQty * $displayPrice);
                                        }

                                        // normalize rawImage -> imgUrl
                                        $imgUrl = null;
                                        if (!empty($rawImage)) {
                                            $rawImage = str_replace('\\', '/', $rawImage);
                                            if (preg_match('#^(https?:)?//#i', $rawImage)) {
                                                $imgUrl = $rawImage;
                                            } elseif (strpos($rawImage, '/') === 0) {
                                                $imgUrl = $rawImage;
                                            } elseif (preg_match('#^storage/#i', $rawImage)) {
                                                $imgUrl = asset($rawImage);
                                            } elseif (preg_match('#^(img|assets)/#i', $rawImage)) {
                                                $imgUrl = asset($rawImage);
                                            } else {
                                                $imgClean = preg_replace('#^storage/#i', '', $rawImage);
                                                $imgUrl = asset('storage/' . $imgClean);
                                            }
                                        }
                                    @endphp
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" class="product-thumb" alt="" onerror="this.onerror=null;this.src='{{ asset('img/c1.webp') }}';">
                                    @else
                                        <div class="product-thumb" style="background:#f1f5f9;display:inline-block;"></div>
                                    @endif
                                    <div style="flex:1;">
                                        <div class="small mb-1">{{ Str::limit($displayName ?? 'Producto', 40) }}</div>
                                        <div class="text-muted small">{{ $displayQty }} × S/.{{ number_format($displayPrice,2) }}</div>
                                    </div>
                                    <div class="fw-semibold">S/.{{ number_format($displaySubtotal ?? 0,2) }}</div>
                                </div>
                            @endforeach
                        </div>

                        <hr>
                        @php
                            $viewSubtotal = 0;
                            foreach ($items as $it) {
                                if (is_array($it)) {
                                    $viewSubtotal += ($it['subtotal'] ?? (($it['price'] ?? 0) * ($it['quantity'] ?? 1)));
                                } else {
                                    $viewSubtotal += (($it->price ?? 0) * ($it->quantity ?? 1));
                                }
                            }
                            $viewIgv = round($viewSubtotal * 0.18, 2);
                            $viewTotal = round($viewSubtotal + $viewIgv, 2);
                        @endphp
                        <div class="d-flex justify-content-between"><div class="text-muted">Subtotal</div><div>S/.{{ number_format($viewSubtotal ?? 0,2) }}</div></div>
                        <div class="d-flex justify-content-between"><div class="text-muted">IGV (18%)</div><div>S/.{{ number_format($viewIgv ?? 0,2) }}</div></div>
                        <div class="d-flex justify-content-between mt-3"><div class="fw-semibold">Total</div><div class="fw-bold">S/.{{ number_format($viewTotal ?? 0,2) }}</div></div>

                        <div class="mt-3 small text-muted">Recibirás tu comprobante en tu correo y podrás descargarlo desde Mis pedidos.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ligero de procesamiento -->
    <div id="processingModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1050; align-items:center; justify-content:center;">
        <div style="background:white; padding:24px 28px; border-radius:10px; max-width:420px; width:90%; text-align:center; box-shadow:0 6px 24px rgba(0,0,0,0.16);">
            <div style="font-size:22px; margin-bottom:12px; font-weight:600;">Verificando la compra...</div>
            <div style="margin-bottom:10px; color:#6b7280;">No cierres esta ventana. Estamos procesando tu pago.</div>
            <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
                <div class="spinner-border text-primary" role="status" style="width:2rem; height:2rem;"></div>
            </div>
        </div>
    </div>

    <!-- Modal genérico de alertas -->
    <div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalTitle">Atención</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body" id="alertModalBody"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    @endsection
</x-app-layout>

<script>
    (function(){
        // Helper: select elements
        var form = document.getElementById('checkoutForm');
        var cardInput = document.querySelector('input[name="card_number"]');
        var expiryInput = document.querySelector('input[name="expiry"]');
        var paymentRadios = document.querySelectorAll('input[name="payment_method"]');
        var cardFields = document.getElementById('cardFields');
        var yapeFields = document.getElementById('yapeFields');
        var processingModal = document.getElementById('processingModal');

        function showProcessing(){ processingModal.style.display = 'flex'; }
        function hideProcessing(){ processingModal.style.display = 'none'; }

        // Generic alert modal helper (Bootstrap modal fallback to alert)
        function showAlert(title, message){
            try {
                var modalEl = document.getElementById('alertModal');
                if (!modalEl) throw new Error('no-modal');
                var mt = document.getElementById('alertModalTitle');
                var mb = document.getElementById('alertModalBody');
                if (mt) mt.textContent = title || 'Atención';
                if (mb) mb.textContent = message || '';
                var bsModal = new bootstrap.Modal(modalEl);
                bsModal.show();
            } catch (e) {
                alert(message || title || '¡Atención!');
            }
        }

        // Toggle payment method UI
        var submitBtnText = document.getElementById('submitBtnText');
        function updatePaymentUI(){
            var sel = document.querySelector('input[name="payment_method"]:checked');
            if (!sel) return;
            if (sel.value === 'yape'){
                cardFields.style.display = 'none';
                yapeFields.style.display = 'block';
                if (submitBtnText) submitBtnText.textContent = 'Enviar y guardar';
                // require receipt when Yape selected
                var receipt = document.getElementById('yapeReceiptInput'); if (receipt) receipt.required = true;
            } else {
                cardFields.style.display = 'block';
                yapeFields.style.display = 'none';
                if (submitBtnText) submitBtnText.textContent = 'Pagar ahora';
                var receipt = document.getElementById('yapeReceiptInput'); if (receipt) receipt.required = false;
            }
        }

        paymentRadios.forEach(function(r){ r.addEventListener('change', updatePaymentUI); });
        // init UI
        updatePaymentUI();

        // Stepper / navegación entre pasos
        var steps = Array.prototype.slice.call(document.querySelectorAll('.stepper .step'));
        var stepEls = [document.getElementById('step-1'), document.getElementById('step-2'), document.getElementById('step-3')];
        var currentStep = 0;

        function showStep(idx){
            currentStep = idx;
            stepEls.forEach(function(el,i){ el.style.display = (i===idx)?'block':'none'; });
            steps.forEach(function(s,i){ s.classList.toggle('active', i===idx); });
        }

        // Document type UI: toggle DNI / RUC inputs and apply client-side restrictions
        var docRadios = document.querySelectorAll('input[name="document_type"]');
        var dniInputEl = document.getElementById('dniInput');
        var rucInputEl = document.getElementById('rucInput');
        var dniGroup = document.getElementById('dniGroup');
        var rucGroup = document.getElementById('rucGroup');

        function onlyDigitsInput(el, maxLen){
            el.addEventListener('input', function(e){
                var v = (this.value || '').replace(/\D/g, '');
                if (maxLen) v = v.slice(0, maxLen);
                this.value = v;
            });
        }

        function updateDocumentUI(){
            var sel = document.querySelector('input[name="document_type"]:checked');
            var type = sel ? sel.value : 'boleta';
            if (type === 'factura'){
                // show RUC, hide DNI
                if (rucGroup) rucGroup.classList.remove('d-none');
                if (dniGroup) dniGroup.classList.add('d-none');
                if (rucInputEl) { rucInputEl.required = true; rucInputEl.focus(); }
                if (dniInputEl) { dniInputEl.required = false; dniInputEl.value = ''; }
            } else {
                // boleta
                if (rucGroup) rucGroup.classList.add('d-none');
                if (dniGroup) dniGroup.classList.remove('d-none');
                if (dniInputEl) { dniInputEl.required = true; dniInputEl.focus(); }
                if (rucInputEl) { rucInputEl.required = false; rucInputEl.value = ''; }
            }
        }

        // apply digit-only restrictions
        if (dniInputEl) onlyDigitsInput(dniInputEl, 8);
        if (rucInputEl) onlyDigitsInput(rucInputEl, 11);
        docRadios.forEach(function(r){ r.addEventListener('change', updateDocumentUI); });
        // init
        updateDocumentUI();

        // buttons
        var toStep2Btn = document.getElementById('toStep2Btn');
        var backToStep1Btn = document.getElementById('backToStep1Btn');
        var toStep3Btn = document.getElementById('toStep3Btn');
        var backToStep2Btn = document.getElementById('backToStep2Btn');

        // validate basic info before moving to payment data
        if (toStep2Btn){
            toStep2Btn.addEventListener('click', function(){
                var name = (form.querySelector('input[name="customer_name"]')||{}).value || '';
                var paySelected = !!document.querySelector('input[name="payment_method"]:checked');
                if (!name.trim()){ showAlert('Atención', 'Por favor ingresa tu nombre completo.'); return; }
                if (!paySelected){ showAlert('Atención', 'Selecciona un método de pago.'); return; }
                // go to step 2 and ensure payment UI updated
                updatePaymentUI();
                showStep(1);
            });
        }

        if (backToStep1Btn){ backToStep1Btn.addEventListener('click', function(){ showStep(0); }); }

        // validate payment inputs before confirmation
        if (toStep3Btn){
            toStep3Btn.addEventListener('click', function(){
                var sel = document.querySelector('input[name="payment_method"]:checked');
                if (!sel){ showAlert('Atención', 'Selecciona un método de pago.'); return; }
                // if card, basic validation
                if (sel.value !== 'yape'){
                    var cardNum = (form.querySelector('input[name="card_number"]')||{}).value || '';
                    var digits = (cardNum||'').replace(/\D/g,'');
                    var expiry = (form.querySelector('input[name="expiry"]')||{}).value || '';
                    var cvc = (form.querySelector('input[name="cvc"]')||{}).value || '';
                    if (digits.length < 12){ showAlert('Pago inválido', 'Número de tarjeta inválido.'); return; }
                    if (expiry.replace(/\D/g,'').length < 4){ showAlert('Pago inválido', 'Fecha de expiración inválida.'); return; }
                    if (cvc.replace(/\D/g,'').length < 3){ showAlert('Pago inválido', 'CVC inválido.'); return; }
                } else {
                    // Yape: require receipt upload before proceeding
                    var fileInput = document.getElementById('yapeReceiptInput');
                    if (!fileInput || !fileInput.files || fileInput.files.length === 0){
                        showAlert('Comprobante requerido', 'Debe subir el comprobante para pagos por Yape antes de continuar.');
                        return;
                    }
                }

                // rellenar resumen de confirmación
                var confName = document.getElementById('confName');
                var confDoc = document.getElementById('confDoc');
                var confPhone = document.getElementById('confPhone');
                var confMethod = document.getElementById('confMethod');
                if (confName) confName.textContent = (form.querySelector('input[name="customer_name"]')||{}).value || '';
                var docType = (document.querySelector('input[name="document_type"]:checked')||{}).value || '';
                var docVal = '';
                if (docType === 'factura') docVal = (document.getElementById('rucInput')||{}).value || '';
                else docVal = (document.getElementById('dniInput')||{}).value || '';
                if (confDoc) confDoc.textContent = docType + (docVal ? ' - ' + docVal : '');
                if (confPhone) confPhone.textContent = (document.getElementById('phoneInput')||{}).value || '';
                var methodLabel = sel.value;
                var map = { 'bcp_card':'Tarjeta BCP', 'interbank_card':'Interbank', 'bbva_card':'BBVA', 'yape':'Yape' };
                if (confMethod) confMethod.textContent = map[methodLabel] || methodLabel;

                showStep(2);
            });
        }

        if (backToStep2Btn){ backToStep2Btn.addEventListener('click', function(){ showStep(1); }); }

        // Card number formatting (group by 4) with paste/caret preservation
        if (cardInput){
            function onlyDigits(str){ return (str || '').replace(/\D/g,''); }
            function formatCardDigits(digits){ return digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim(); }

            cardInput.addEventListener('input', function(e){
                var val = this.value;
                var selStart = this.selectionStart || val.length;
                var digitsBefore = onlyDigits(val.slice(0, selStart)).length;

                var digits = onlyDigits(val).slice(0,16);
                var formatted = formatCardDigits(digits);
                this.value = formatted;

                var pos = 0; var counted = 0;
                for (var i=0;i<formatted.length;i++){
                    if (/\d/.test(formatted.charAt(i))) counted++;
                    pos++;
                    if (counted >= digitsBefore) break;
                }
                if (counted < digitsBefore) pos = formatted.length;
                this.setSelectionRange(pos, pos);
            });

            cardInput.addEventListener('paste', function(e){
                e.preventDefault();
                var text = (e.clipboardData || window.clipboardData).getData('text');
                var digits = onlyDigits(text).slice(0,16);
                var selStart = this.selectionStart || 0;
                var selEnd = this.selectionEnd || 0;
                var before = onlyDigits(this.value.slice(0, selStart));
                var after = onlyDigits(this.value.slice(selEnd));
                var newDigits = (before + digits + after).slice(0,16);
                this.value = formatCardDigits(newDigits);
                var newPosDigits = before.length + digits.length;
                var pos = 0; var counted = 0; var formatted = this.value;
                for (var i=0;i<formatted.length;i++){
                    if (/\d/.test(formatted.charAt(i))) counted++;
                    pos++;
                    if (counted >= newPosDigits) break;
                }
                if (counted < newPosDigits) pos = formatted.length;
                this.setSelectionRange(pos, pos);
            });
        }

        // Expiry input formatting MM/AA (preserve caret)
        if (expiryInput){
            expiryInput.setAttribute('placeholder','MM/AA');
            expiryInput.addEventListener('input', function(e){
                var v = this.value.replace(/\D/g,'').slice(0,4); // MMYY
                var res = v;
                if (v.length >= 3) res = v.slice(0,2) + '/' + v.slice(2);
                else if (v.length >= 1 && v.length <=2) res = v;
                // preserve caret: simple strategy -> put at end
                this.value = res;
            });
            expiryInput.addEventListener('paste', function(e){
                e.preventDefault();
                var text = (e.clipboardData || window.clipboardData).getData('text');
                var digits = (text || '').replace(/\D/g,'').slice(0,4);
                if (digits.length <=2) this.value = digits;
                else this.value = digits.slice(0,2) + '/' + digits.slice(2);
            });
        }

        // Submit handler: show modal and send AJAX; works for Yape and card
        if (form){
            form.addEventListener('submit', function(e){
                e.preventDefault();
                var fd = new FormData(form);
                showProcessing();

                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                var csrf = csrfMeta ? csrfMeta.getAttribute('content') : '';
                fetch(form.action, {
                    method: form.method || 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: fd,
                    credentials: 'same-origin'
                }).then(function(res){
                    return res.json().catch(function(){ return { success:false, message: 'Respuesta inválida del servidor' }; });
                }).then(function(json){
                    if (json && json.success){
                        // si viene redirect, navegar
                        if (json.redirect) return window.location = json.redirect;
                        // fallback: abrir success route con order id
                        if (json.order_id) return window.location = '/checkout/success?order=' + json.order_id;
                        return window.location.reload();
                    }
                    // mostrar errores
                    hideProcessing();
                    var msg = (json && json.message) ? json.message : 'Ocurrió un error al procesar el pago';
                    showAlert('Error', msg);
                }).catch(function(err){
                    hideProcessing();
                    console.error(err);
                    showAlert('Error de red', 'Error de red. Intenta nuevamente.');
                });
            });
        }
    })();
</script>