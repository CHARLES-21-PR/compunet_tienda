<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 col-md-3 px-0">
                    @include('admin.partials.nav_cate')
                </div>
                <div id="settings-main" class="col-12 col-md-9 ps-md-1">
                    <div class="bg-dark p-3" style="border-radius:14px;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h1 class="text-white mb-0">Dashboard</h1>
                               
                            </div>
                        </div>

                       

                        {{-- Categories list (compact). Card bg: red if any product low-stock, green if all fine. --}}
                        <div class="mt-3">
                            <h2 class="h5 text-white mb-2">Categorías</h2>
                            <div class="row">
                                @forelse($categories as $cat)
                                    @php
                                        $products = collect($cat->products ?? []);

                                        // Load thresholds from config (defaults if not set)
                                        $lowMax = config('stock.thresholds.low', 5);
                                        $midMax = config('stock.thresholds.mid', 10);
                                        $lowMin = 1; // stock > 0 considered for low
                                        $midMin = $lowMax + 1;

                                        // Build numeric stocks collection (ignore non-numeric stock values)
                                        $stocks = $products->map(function($p){
                                            $s = $p->stock ?? null;
                                            if (is_numeric($s)) return intval($s);
                                            if (is_string($s)) {
                                                $num = preg_replace('/[^0-9\-]/', '', $s);
                                                return $num === '' ? null : intval($num);
                                            }
                                            return null;
                                        })->filter(function($v){ return $v !== null; })->values();

                                        // Prefer the aggregated min stock provided by the query
                                        // (added via withMin('products','stock') in the controller).
                                        // Fallback to the eager-loaded products collection if not present.
                                        if (isset($cat->products_min_stock) && is_numeric($cat->products_min_stock)) {
                                            $minStock = intval($cat->products_min_stock);
                                        } else {
                                            $minStock = $stocks->count() ? $stocks->min() : null;
                                        }
                                        $maxStock = $stocks->count() ? $stocks->max() : null;

                                        // Determine counts for detail view
                                        $lowProducts = $products->filter(function($p) use ($lowMin, $lowMax) { $s = is_numeric($p->stock ?? null) ? intval($p->stock) : null; return $s !== null && $s >= $lowMin && $s <= $lowMax; })->values();
                                        $midProducts = $products->filter(function($p) use ($midMin, $midMax) { $s = is_numeric($p->stock ?? null) ? intval($p->stock) : null; return $s !== null && $s >= $midMin && $s <= $midMax; })->values();
                                        $hasLow = $lowProducts->isNotEmpty();
                                        $hasMid = $midProducts->isNotEmpty();

                                        // Colors from config with sensible defaults
                                        $colors = config('stock.colors', [
                                            'zero' => 'linear-gradient(90deg,#6b7280,#9ca3af)',
                                            'low'  => 'linear-gradient(90deg,#dc2626,#ef4444)',
                                            'mid'  => 'linear-gradient(90deg,#f59e0b,#d97706)',
                                            'ok'   => 'linear-gradient(90deg,#10b981,#059669)',
                                        ]);

                                        // Decide background based on minStock (most conservative):
                                        // - if no numeric stocks or all stocks <= 0 -> 'zero'
                                        // - if minStock <= lowMax and minStock > 0 -> 'low'
                                        // - elseif minStock <= midMax -> 'mid'
                                        // - else 'ok'
                                        if ($minStock === null) {
                                            $bg = $colors['zero'];
                                            $allZero = true;
                                        } else {
                                            $allZero = ($stocks->filter(fn($s)=> $s > 0)->isEmpty());
                                            if ($allZero) {
                                                $bg = $colors['zero'];
                                            } elseif ($minStock <= $lowMax && $minStock > 0) {
                                                $bg = $colors['low'];
                                            } elseif ($minStock <= $midMax) {
                                                $bg = $colors['mid'];
                                            } else {
                                                $bg = $colors['ok'];
                                            }
                                        }
                                        // For debugging: expose computed values when ?debug_dashboard=1 is present
                                        $debugDashboard = request()->get('debug_dashboard');
                                        // note: $minStock may already come from products_min_stock
                                        $computedMinFromCollection = $products->filter(fn($p)=> is_numeric($p->stock ?? null))->min(fn($p)=> intval($p->stock ?? 0));
                                        $computedMaxFromCollection = $products->filter(fn($p)=> is_numeric($p->stock ?? null))->max(fn($p)=> intval($p->stock ?? 0));
                                        $totalCount = $products->count();
                                        $lowCount = $lowProducts->count();
                                        $midCount = $midProducts->count();
                                    
                                    @endphp
                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                        <div class="p-3 h-100 d-flex flex-column justify-content-center align-items-center text-center" style="border-radius:10px; border:1px solid rgba(255,255,255,0.03); background: {{ $bg }} !important;" data-chosen-bg="{{ $bg }}">
                                            <div class="h3 text-white mb-1">{{ $cat->products_count ?? 0 }}</div>
                                            <div class="text-white-75 small mb-2">productos</div>
                                            <div class="h6 text-white mb-0">{{ $cat->name }}</div>
                                                @if($debugDashboard)
                                                    <div class="mt-2 text-start w-100 small" style="background:rgba(255,255,255,0.06); padding:6px;border-radius:6px;color:#fff;">
                                                        <div>total: {{ $totalCount }} — min: {{ $minStock ?? 'n/a' }} — max: {{ $maxStock ?? 'n/a' }}</div>
                                                        <div>low (<= {{ $lowMax }}): {{ $lowCount }} — mid ({{ $midMin }}-{{ $midMax }}): {{ $midCount }}</div>
                                                        <div>allZero: {{ $allZero ? 'yes' : 'no' }} — chosen color: {{ $bg }}</div>
                                                    </div>
                                                @endif
                                            @php
                                                $showDetails = $hasLow || $hasMid;
                                                $detailsProducts = $hasLow ? $lowProducts : ($hasMid ? $midProducts : collect());
                                            @endphp
                                            <div class="mt-2">
                                                @if($showDetails || (isset($cat->products_min_stock) && is_numeric($cat->products_min_stock) && intval($cat->products_min_stock) <= $midMax))
                                                    <button class="btn btn-sm btn-light text-dark btn-show-low mt-2" type="button" data-low='@json($detailsProducts)' data-category-id="{{ $cat->id }}" data-category-name="{{ $cat->name }}" data-products-min="{{ $cat->products_min_stock ?? '' }}" data-low-max="{{ $lowMax }}" data-mid-max="{{ $midMax }}">Ver detalles</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-muted">No hay categorías o productos.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        <h2 class="h5 text-white mb-3">Pedidos recientes y estadísticas</h2>
                         <div style="margin-top:14px;">
                        
                            <div style="background: rgba(255,255,255,0.02); padding:0 16px; border-radius:12px; box-shadow: 0 6px 18px rgba(2,6,23,0.45); margin-bottom:18px; border:1px solid rgba(255,255,255,0.03);" class="mt-3">
                            <strong class="text-white">Pedidos por día</strong>
                            <div class="d-flex flex-wrap mt-2" style="gap:8px;">
                                @if(!empty($ordersCountByDay) && $ordersCountByDay->count())
                                    @foreach($ordersCountByDay as $date => $count)
                                        <div style="background: linear-gradient(135deg,#475569,#0f172a); padding:8px 12px; border-radius:10px; min-width:110px; color:white; border:1px solid rgba(255,255,255,0.03);">
                                            <div class="small" style="opacity:0.9">{{ $date }}</div>
                                            <div class="h5 mb-0">{{ $count }}</div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-white-50 small">No hay datos para las fechas seleccionadas.</div>
                                @endif
                            </div>
                         </div>
                        {{-- Orders widget --}}
                       <div style="background: rgba(255,255,255,0.02); padding:0 16px; border-radius:12px; box-shadow: 0 6px 18px rgba(2,6,23,0.45); margin-bottom:18px; border:1px solid rgba(255,255,255,0.03);">
                            
    <form id="dashboard-filter-form" action="{{ route('admin.dashboard.index') }}" method="GET" class="d-flex flex-wrap align-items-end" style="gap:12px;">
        <h3 class="h6 text-white w-100 mb-2">Filtrar pedidos por fecha</h3>
        <div class="flex flex-col">
                 <label for="start_date" class="text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Fecha Inicio</label>
                 <input type="date" 
                     name="start_date" 
                     id="start_date"
                     value="{{ $start ?? request('start_date') ?? now()->format('Y-m-d') }}"
                     class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" style="background:#ffffff; color:#0f172a;">
        </div>

        <div class="flex flex-col">
                 <label for="end_date" class="text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Fecha Fin</label>
                 <input type="date" 
                     name="end_date" 
                     id="end_date"
                     value="{{ $end ?? request('end_date') ?? now()->format('Y-m-d') }}"
                     class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" style="background:#ffffff; color:#0f172a;">
        </div>

        <div class="flex flex-col min-w-[200px]">
            <label for="client_id" class="text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">Usuario / Cliente</label>
                <select name="client_id" 
                    id="client_id" 
         class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" style="background:#fff; color:#0f172a;">
                <option value="">-- Todos los usuarios --</option>
                @if(!empty($clients))
                    @foreach($clients as $user)
                        <option value="{{ $user->id }}" {{ request('client_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <div class="d-flex" style="gap:8px;">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="bi bi-funnel" aria-hidden="true" style="margin-right:6px"></i>Filtrar
            </button>

            @if(request()->hasAny(['start_date', 'end_date', 'client_id']))
                <a id="client-clear" href="{{ route('admin.dashboard.index') }}" class="btn btn-sm btn-outline-light">Limpiar</a>
            @endif
        </div>

    </form>
</div>

                {{-- Orders aggregation and results --}}
                

                        <div class="mt-3">
                        <strong class="text-white">Resultados</strong>
                        <div class="d-flex justify-content-end mb-2">
                            <div class="settings-pagination-top">
                                {!! str_replace('<nav', '<nav style="background: rgba(33, 37, 41, 0.75); padding: .15rem .5rem; border-radius:10px; color: #fff; --bs-pagination-color: #fff; --bs-pagination-bg: transparent; --bs-pagination-border-color: rgba(255,255,255,0.06); --bs-pagination-hover-color: #fff; --bs-pagination-hover-bg: rgba(255,255,255,0.04); --bs-pagination-active-color: #0f172a; --bs-pagination-active-bg: #eef2ff;"', $recentOrders->links('pagination::bootstrap-5')) !!}
                            </div>
                        </div>
                        <div id="orders-cards" class="row g-3 mt-2">
                            @forelse($recentOrders as $r)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="p-3" style="background: rgba(255,255,255,0.02); border-radius:12px; border:1px solid rgba(255,255,255,0.04); box-shadow: 0 6px 14px rgba(0,0,0,0.5);">
                                        <div class="d-flex align-items-start">
                                            <div style="width:48px; height:48px; border-radius:8px; background:linear-gradient(135deg,#667eea,#764ba2); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; margin-right:12px;">
                                                <?php
                                                    $uname = optional($r->user)->name ?? 'U';
                                                    $parts = preg_split('/\s+/', trim($uname));
                                                    $initials = strtoupper(substr($parts[0] ?? $uname,0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
                                                ?>
                                                {{ $initials }}
                                            </div>
                                            <div class="w-100">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <div class="h6 text-white mb-0">Orden #{{ $r->id }} <small class="text-white-50">• {{ $r->created_at->format('Y-m-d H:i') }}</small></div>
                                                        <div class="small text-white-50">{{ optional($r->user)->name }} — {{ optional($r->user)->email }}</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div style="margin-bottom:6px;">
                                                            <?php
                                                                $raw = strtolower(trim($r->status ?? ''));
                                                                $map = [
                                                                    'paid' => 'pagado', 'pagado' => 'pagado',
                                                                    'pending' => 'pendiente', 'pendiente' => 'pendiente',
                                                                    'failed' => 'fallido', 'fallido' => 'fallido'
                                                                ];
                                                                $key = $map[$raw] ?? $raw;
                                                                $labels = ['pagado' => 'Pagado', 'pendiente' => 'Pendiente', 'fallido' => 'Fallido'];
                                                                $label = $labels[$key] ?? ucfirst($raw);
                                                                $bg = $key == 'pagado' ? 'linear-gradient(90deg,#16a34a,#059669)' : ($key == 'pendiente' ? 'linear-gradient(90deg,#f59e0b,#d97706)' : 'linear-gradient(90deg,#ef4444,#dc2626)');
                                                            ?>
                                                            <span class="badge" style="background: {{ $bg }}; color:#fff; border-radius:10px; padding:.35rem .6rem; box-shadow:0 4px 10px rgba(0,0,0,0.25);">{{ $label }}</span>
                                                        </div>
                                                        <div class="small text-white">S/. {{ number_format($r->total ?? 0,2) }}</div>
                                                    </div>
                                                </div>
                                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                                    <div class="small text-white-50">Items: {{ optional($r->items)->count() ?? '-' }}</div>
                                                        <div>
                                                        <a href="{{ route('admin.orders.show', $r) }}" class="btn btn-sm btn-outline-light order-view" data-url="{{ route('admin.orders.show', $r) }}">Ver</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-white-50 small">No hay pedidos en este rango.</div>
                            @endforelse
                        </div>
                        
                        </div>
                    </div>
                </div>

                    </div>
                </div>
            </div>
        </div>
    <script>
        (function(){
            // If select-based client filter exists, submit form on change. Also wire clear button.
            (function(){
                const clearBtn = document.getElementById('client-clear');
                const clientSelect = document.querySelector('select[name="client_id"]');
                // Find the closest form for the select to avoid selecting an unrelated form
                const form = clientSelect ? clientSelect.closest('form') : document.querySelector('form');

                if (clientSelect) {
                    clientSelect.addEventListener('change', function(){
                        try {
                            // Build a query string based on current inputs to ensure parameters are sent reliably
                            const params = new URLSearchParams(window.location.search);
                            // copy start_date/end_date if present in form
                            const start = form.querySelector('input[name="start_date"]')?.value || '';
                            const end = form.querySelector('input[name="end_date"]')?.value || '';
                            if (start) params.set('start_date', start); else params.delete('start_date');
                            if (end) params.set('end_date', end); else params.delete('end_date');
                            if (this.value) params.set('client_id', this.value); else params.delete('client_id');
                            const base = form.getAttribute('action') || window.location.pathname;
                            window.location.href = base + '?' + params.toString();
                        } catch (e) {
                            // fallback to normal submit
                            if (form) form.submit();
                        }
                    });
                }
                if (clearBtn) {
                    clearBtn.addEventListener('click', function(){
                        try {
                            const params = new URLSearchParams(window.location.search);
                            params.delete('client_id');
                            const start = form.querySelector('input[name="start_date"]')?.value || '';
                            const end = form.querySelector('input[name="end_date"]')?.value || '';
                            if (start) params.set('start_date', start); else params.delete('start_date');
                            if (end) params.set('end_date', end); else params.delete('end_date');
                            const base = form.getAttribute('action') || window.location.pathname;
                            window.location.href = base + '?' + params.toString();
                        } catch (e) {
                            if (clientSelect) clientSelect.value = '';
                            if (form) form.submit();
                        }
                    });
                }

                // Ensure the Filtrar submit builds the same URL (avoids issues where form submission
                // might not include the client parameter due to JS or multiple forms on page).
                const dashboardForm = document.getElementById('dashboard-filter-form');
                if (dashboardForm) {
                    dashboardForm.addEventListener('submit', function(ev){
                        try {
                            ev.preventDefault();
                            const params = new URLSearchParams(window.location.search);
                            const start = dashboardForm.querySelector('input[name="start_date"]')?.value || '';
                            const end = dashboardForm.querySelector('input[name="end_date"]')?.value || '';
                            const client = dashboardForm.querySelector('select[name="client_id"]')?.value || '';
                            if (start) params.set('start_date', start); else params.delete('start_date');
                            if (end) params.set('end_date', end); else params.delete('end_date');
                            if (client) params.set('client_id', client); else params.delete('client_id');
                            const base = dashboardForm.getAttribute('action') || window.location.pathname;
                            window.location.href = base + (params.toString() ? ('?' + params.toString()) : '');
                        } catch (e) {
                            // fallback to normal submit
                            dashboardForm.submit();
                        }
                    });
                }
            })();
        })();
    </script>
        <!-- Order modal -->
        <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content bg-dark text-white">
                    <div class="modal-header">
                        <h5 class="modal-title" id="orderModalLabel">Detalle de pedido</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" id="orderModalBody">
                        <div class="text-center text-white-50">Cargando...</div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
                (function(){
                        // Attach click handlers to dynamic order view links
                        function openOrderModal(url) {
                                const modalEl = document.getElementById('orderModal');
                                const modalBody = document.getElementById('orderModalBody');
                                if (!modalEl || !modalBody) return;
                                modalBody.innerHTML = '<div class="text-center text-white-50">Cargando...</div>';
                                const bsModal = new bootstrap.Modal(modalEl);
                                bsModal.show();
                                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                                        .then(res => {
                                                if (!res.ok) throw new Error('Error cargando pedido');
                                                return res.text();
                                        })
                                        .then(html => {
                                                modalBody.innerHTML = html;
                                        })
                                        .catch(err => {
                                                modalBody.innerHTML = '<div class="text-danger">No se pudo cargar el pedido.</div>';
                                                console.error(err);
                                        });
                        }

                        document.addEventListener('click', function(e){
                                const el = e.target.closest && e.target.closest('.order-view');
                                if (!el) return;
                                e.preventDefault();
                                const url = el.getAttribute('data-url') || el.getAttribute('href');
                                if (!url) return;
                                openOrderModal(url);
                        });
                })();
        </script>
                <!-- Low stock modal -->
                <div class="modal fade" id="lowStockModal" tabindex="-1" aria-labelledby="lowStockModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content bg-dark text-white">
                            <div class="modal-header">
                                <h5 class="modal-title" id="lowStockModalLabel">Productos con stock bajo</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body" id="lowStockModalBody">
                                <div class="text-white-50">Cargando...</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-outline-light d-none" id="lowStockBackBtn">Volver</button>
                                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                        (function(){
                                const editBase = '{{ url('/settings/products') }}';
                                function openLowStockModal(categoryName, lowProducts) {
                                        const modalEl = document.getElementById('lowStockModal');
                                        const modalBody = document.getElementById('lowStockModalBody');
                                        const modalTitle = document.getElementById('lowStockModalLabel');
                                        if (!modalEl || !modalBody) return;
                                        modalTitle.textContent = 'Productos con stock bajo — ' + (categoryName || '');
                                        if (!Array.isArray(lowProducts) || !lowProducts.length) {
                                                modalBody.innerHTML = '<div class="text-white-50">No se encontraron productos con stock bajo.</div>';
                                        } else {
                                                let html = '<div class="list-group">';
                                                lowProducts.forEach(p => {
                                                        const id = p.id || p.id === 0 ? p.id : '';
                                                        const name = p.name || '—';
                                                        const stock = typeof p.stock !== 'undefined' ? parseInt(p.stock) : 0;
                                                        const editUrl = id ? (editBase + '/' + id + '/edit') : '#';
                                                        html += '<div class="list-group-item bg-transparent d-flex justify-content-between align-items-center">'
                                                            + '<div class="me-3"><div class="fw-semibold text-white">'+name+'</div><div class="small text-white-50">ID: '+id+'</div></div>'
                                                            + '<div class="text-end">'
                                                                    + '<div class="mb-2 small ' + (stock<=5 ? 'text-warning' : 'text-white-50') + '">Cantidad: '+stock+'</div>'
                                                                    + '<button class="btn btn-sm btn-outline-light btn-edit-product" data-edit-url="'+editUrl+'">Editar</button>'
                                                                    + '</div></div>';
                                                });
                                                html += '</div>';
                                                    modalBody.innerHTML = html;
                                                    // store original list html so we can return after editing
                                                    try { modalEl.dataset.originalList = html; } catch (e) {}
                                        }
                                        const bs = new bootstrap.Modal(modalEl);
                                        bs.show();
                                }

                                document.addEventListener('click', function(e){
                                    const btn = e.target.closest && e.target.closest('.btn-show-low');
                                    if (!btn) return;
                                    e.preventDefault();
                                    const raw = btn.getAttribute('data-low') || '[]';
                                    let low = [];
                                    try { low = JSON.parse(raw); } catch (err) { low = []; }
                                    const catName = btn.getAttribute('data-category-name') || '';
                                    const catId = btn.getAttribute('data-category-id');
                                    // If we have no embedded low items but we have a category id,
                                    // fetch the low products from the server endpoint.
                                        if ((Array.isArray(low) && low.length === 0) && catId) {
                                                const base = '{{ url('/settings/categories') }}';
                                                const prodMin = parseInt(btn.getAttribute('data-products-min'));
                                                const lowMaxAttr = parseInt(btn.getAttribute('data-low-max'));
                                                const midMaxAttr = parseInt(btn.getAttribute('data-mid-max'));
                                                let url = base + '/' + encodeURIComponent(catId) + '/low-products';
                                                // If min stock indicates mid range, request level=mid
                                                if (!isNaN(prodMin) && !isNaN(lowMaxAttr) && !isNaN(midMaxAttr) && prodMin > lowMaxAttr && prodMin <= midMaxAttr) {
                                                    url += '?level=mid';
                                                }
                                                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                                                    .then(res => res.json())
                                                    .then(json => {
                                                        const items = (json && json.ok && Array.isArray(json.items)) ? json.items : [];
                                                        openLowStockModal(catName, items);
                                                    })
                                                    .catch(err => {
                                                        console.error('Error loading low products', err);
                                                        openLowStockModal(catName, []);
                                                    });
                                        } else {
                                                openLowStockModal(catName, low);
                                        }
                                });
                                // Handle edit-in-modal button clicks (event delegation)

                                document.addEventListener('click', function(e){
                                            const editBtn = e.target.closest && e.target.closest('.btn-edit-product');
                                            if (!editBtn) return;
                                            e.preventDefault();
                                            const editUrl = editBtn.getAttribute('data-edit-url');
                                            if (!editUrl) return;
                                            // load edit form into the same modal
                                            const modalEl = document.getElementById('lowStockModal');
                                            const modalBody = document.getElementById('lowStockModalBody');
                                            const modalTitle = document.getElementById('lowStockModalLabel');
                                            if (!modalEl || !modalBody) return;
                                            modalBody.innerHTML = '<div class="text-center text-white-50">Cargando formulario...</div>';
                                            fetch(editUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                                                .then(res => res.text())
                                                .then(html => {
                                                    modalBody.innerHTML = html;
                                                    // show back button
                                                    const back = document.getElementById('lowStockBackBtn');
                                                    if (back) back.classList.remove('d-none');
                                                    // wire back
                                                    if (back) back.onclick = function(){
                                                        try { modalBody.innerHTML = modalEl.dataset.originalList || ''; back.classList.add('d-none'); } catch (e) {}
                                                    };
                                                })
                                                .catch(err => {
                                                    modalBody.innerHTML = '<div class="text-danger">No se pudo cargar el formulario.</div>';
                                                });
                                        });
                                    })();
                </script>
        <script>
            // Debug helper: force-applies the chosen background and log computed styles
            (function(){
                try{
                    const els = document.querySelectorAll('[data-chosen-bg]');
                    els.forEach((el, idx)=>{
                        const chosen = el.getAttribute('data-chosen-bg');
                        if(!chosen) return;
                        // Force apply with priority important
                        try{ el.style.setProperty('background', chosen, 'important'); } catch(e){}
                        const cs = window.getComputedStyle(el).backgroundColor || window.getComputedStyle(el).background;
                        console.groupCollapsed('dashboard-widget['+idx+'] ' + (el.querySelector('.h6') ? el.querySelector('.h6').textContent.trim() : '')); 
                        console.log('data-chosen-bg:', chosen);
                        console.log('computed background:', cs);
                        console.log('inline style:', el.getAttribute('style'));
                        console.groupEnd();
                    });
                    if(!els.length) console.log('dashboard: no widgets with data-chosen-bg found');
                }catch(err){ console.error('dashboard debug helper error', err); }
            })();
        </script>


    @endsection
</x-app-layout>
