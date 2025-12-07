<header class="header-top">
    <div class="redes">
        <div class="redes-var gap-2">
            <a href="#"><img class="ico" src="/img/ins.webp" alt="" srcset=""></a>
            <a href="#"><img class="ico" src="/img/face.webp" alt="" srcset=""></a>
        </div>
        <div class="redes-1">
            <a href="{{ route('dashboard') }}">Inicio</a>
            <a href="{{ route('nuestras_tiendas') }}">Nuestras tiendas</a>
            <a href="">Contáctanos</a>
        </div>
    </div>
</header>

<nav>
    <div class="logo">
        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
    </div>

    <div class="nav-1">
        <div class="enlace enlace-show">
            <a class="menu_link" href=""><img class="icon1" src="/img/l1.webp" alt="">Equipos de computo<img class="arrow" src="/assets/arrow.svg" alt=""></a>
            <ul class="menu_nesting">
                <li class="menu_inside">
                    <a href="{{ route('categories.show', ['category' => 'computadoras']) }}" class="menu_link menu_link--inside">
                        <img class="icon2" src="/img/a1.webp" alt="">Computadoras
                    </a>
                </li>
                <li class="menu_inside">
                    <a href="{{ route('categories.show', ['category' => 'laptops']) }}" class="menu_link menu_link--inside">
                        <img class="icon2" src="/img/a2.webp" alt="">Laptops
                    </a>
                </li>
                <li class="menu_inside">
                    <a href="{{ route('categories.show', ['category' => 'tablets']) }}" class="menu_link menu_link--inside"><img class="icon2" src="/img/a3.webp" alt="">Tablets</a>
                </li>
            </ul>
        </div>
        <div class="enlace ">
            <a class="menu_link" href="{{ route('categories.show', ['category' => 'impresoras']) }}"><img class="icon1" src="/img/l2.webp" alt="">Impresoras</a>
        </div>
        <div class="enlace enlace-show">
            <a class="menu_link" href="#"><img class="icon1" src="/img/l3.webp" alt="">Catálogos<img class="arrow" src="/assets/arrow.svg" alt=""></a>
            <ul class="menu_nesting">
                <li class="menu_inside">
                    <a href="{{ route('categories.show', ['category' => 'tintas']) }}" class="menu_link menu_link--inside"><img class="icon2" src="/img/a4.webp" alt="">Tintas</a>
                </li>
                <li class="menu_inside">
                    <a href="{{ route('categories.show', ['category' => 'ssd']) }}" class="menu_link menu_link--inside"><img class="icon2" src="/img/a5.webp" alt="">SSD</a>
                </li>
                <li class="menu_inside">
                    <a href="{{ route('categories.show', ['category' => 'combo-gamer']) }}" class="menu_link menu_link--inside"><img class="icon2" src="/img/a6.webp" alt="">COMBO GAMER</a>
                </li>
            </ul>
        </div>
        <div class="enlace ">
            <a class="menu_link" href="{{ route('Internet_Ilimitado') }}"> <img class="icon1" src="/img/l4.webp" alt="">Internet ilimitado</a>
        </div>
        <div class="enlace enlace-show">
            <a class="menu_link" href=""><img class="icon1" src="/img/l5.webp" alt="">Atención especializada<img class="arrow" src="/assets/arrow.svg" alt=""></a>
            <ul class="menu_nesting">
                <li class="menu_inside">
                    <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a7.webp" alt="">Camara de vigilancia</a>
                </li>
                <li class="menu_inside">
                    <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a8.webp" alt="">Soporte técnico</a>
                </li>
                <li class="menu_inside">
                    <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a9.webp" alt="">Nuestros clientes</a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Right side: user dropdown + cart grouped --}}
    @if (Route::has('login'))
        <div class="top-right-actions" style="display:flex;align-items:center;padding:0 20px;gap:16px">
            <nav class="flex items-center justify-end gap-4">
                @auth
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md focus:outline-none transition ease-in-out duration-150">
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                        </svg>
                                    </div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                @php $u = Auth::user(); @endphp
                                @if(isset($u->role) && $u->role === 'cliente')
                                    <x-dropdown-link :href="route('client.orders.index')">{{ __('Mis pedidos') }}</x-dropdown-link>
                                @else
                                    <x-dropdown-link :href="route('profile.edit')">{{ __('Perfil') }}</x-dropdown-link>
                                @endif
                                @role('admin')
                                    <x-dropdown-link :href="route('admin.dashboard.index')">{{ __('Panel admin') }}</x-dropdown-link>
                                @endrole
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Cerrar sesión') }}</x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md focus:outline-none transition ease-in-out duration-150">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                    </svg>
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('login')">{{ __('Iniciar sesión') }}</x-dropdown-link>
                            {{-- <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal">Log in</a> --}}
                            @if (Route::has('register'))
                            <x-dropdown-link :href="route('register')">{{ __('Registrarse') }}</x-dropdown-link>
                                {{-- <a href="{{ route('register') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] rounded-sm text-sm leading-normal">Register</a> --}}
                            @endif
                        </x-slot>
                    </x-dropdown>
                @endauth
            </nav>

            {{-- Notifications (campana) --}}
            @role('admin')
             @php
                $pendingYape = collect();
                $lowStock = collect();
                $notifCount = 0;
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
                        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'payment_method')) {
                            $pendingYape = \App\Models\Order::where('payment_method', 'yape')
                                ->whereIn('status', ['pendiente','pending'])
                                ->latest()->take(6)->get();
                        } elseif (\Illuminate\Support\Facades\Schema::hasTable('payments')) {
                            $pendingYape = \App\Models\Order::whereHas('payments', function($q){
                                $q->where('method','yape');
                            })->whereIn('status',['pendiente','pending'])->latest()->take(6)->get();
                        }
                    }
                } catch (\Throwable $e) {
                    $pendingYape = collect();
                }
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('products') && \Illuminate\Support\Facades\Schema::hasColumn('products','stock')) {
                        $lowStock = \App\Models\Product::where('stock','<=', 5)->orderBy('stock', 'asc')->take(6)->get();
                    }
                } catch (\Throwable $e) {
                    $lowStock = collect();
                }
                $notifCount = ($pendingYape ? $pendingYape->count() : 0) + ($lowStock ? $lowStock->count() : 0);
            @endphp

            <div class="notif-wrapper" style="position:relative;display:inline-block">
                <button id="notif-toggle" class="notif-btn" aria-expanded="false" aria-label="Notificaciones">
                    <span class="notif-icon-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.64 5.36 6 7.92 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span id="notif-badge" class="notif-badge">{{ $notifCount }}</span>
                    </span>
                </button>

                <div id="notif-dropdown" class="notif-dropdown shadow-lg rounded-4 border-0 overflow-hidden" aria-hidden="true" style="width: 320px; right: 0; left: auto;">
                    <div class="notif-dropdown-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                        <div class="fw-bold text-dark">Notificaciones</div>
                        @if($notifCount > 0)
                            <span class="badge bg-danger rounded-pill">{{ $notifCount }}</span>
                        @endif
                    </div>
                    <div class="notif-dropdown-body bg-light" style="max-height: 400px; overflow-y: auto;">
                        @if($pendingYape && $pendingYape->count())
                            <div class="p-2">
                                <div class="small text-muted fw-bold px-2 mb-2 text-uppercase" style="font-size: 0.7rem;">Validación de Pagos</div>
                                @foreach($pendingYape as $o)
                                    <a href="{{ route('admin.orders.show', ['order' => $o->id]) }}" class="d-block text-decoration-none text-dark mb-2">
                                        <div class="card border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-body p-2 d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-phone" viewBox="0 0 16 16">
                                                        <path d="M11 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h6zM5 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H5z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="fw-semibold small">Pedido #{{ $o->id }}</span>
                                                        <span class="badge bg-warning text-dark" style="font-size: 0.6rem;">Pendiente</span>
                                                    </div>
                                                    <div class="small text-muted text-truncate">{{ optional($o->user)->name ?? optional($o->client)->name ?? 'Cliente' }}</div>
                                                    <div class="small text-primary fw-bold">S/. {{ number_format($o->total, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($lowStock && $lowStock->count())
                            <div class="p-2">
                                <div class="small text-muted fw-bold px-2 mb-2 text-uppercase" style="font-size: 0.7rem;">Alerta de Stock</div>
                                @foreach($lowStock as $p)
                                    <a href="{{ route('admin.products.edit', ['product' => $p->id]) }}" class="d-block text-decoration-none text-dark mb-2">
                                        <div class="card border-0 shadow-sm hover-shadow transition-all">
                                            <div class="card-body p-2 d-flex align-items-center gap-3">
                                                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16">
                                                        <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                                    </svg>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="fw-semibold small text-truncate">{{ $p->name ?? 'Producto' }}</div>
                                                    <div class="small text-danger fw-bold">Quedan: {{ $p->stock ?? 'N/A' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if((!$pendingYape || $pendingYape->isEmpty()) && (!$lowStock || $lowStock->isEmpty()))
                            <div class="text-center d-flex flex-column align-items-center justify-content-center py-4 text-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-bell-slash mb-2 opacity-50" viewBox="0 0 16 16">
                                    <path d="M5.164 14H15c-1.5-1-2-5.902-2-7 0-.264-.02-.523-.06-.776L5.164 14zm6.288-10.617A4.988 4.988 0 0 0 8.995 2.1a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 7c0 .898-.335 4.342-1.278 6.113l9.73-9.73zM10 15a2 2 0 1 1-4 0h4zm-9.375.625a.53.53 0 0 0 .75.75l14.75-14.75a.53.53 0 0 0-.75-.75L.625 15.625z"/>
                                </svg>
                                <p class="small mb-0">No hay notificaciones nuevas</p>
                            </div>
                        @endif
                    </div>
                    <div class="notif-dropdown-footer bg-white border-top p-2 text-center">
                        <a href="{{ route('admin.notifications.index') }}" class="text-decoration-none small fw-bold text-primary">Ver todas las notificaciones</a>
                    </div>
                </div>
            </div>
            @endrole
            
            {{-- Cart --}}
            @php
                $cartCount = 0;
                if (\Illuminate\Support\Facades\Auth::check()) {
                    // Authenticated user: count from DB
                    $cartCount = \App\Models\shoppingCart::where('user_id', \Illuminate\Support\Facades\Auth::id())->sum('quantity');
                } else {
                    // Guest: count from Session
                    $cart = session('cart', []);
                    if (is_array($cart)) {
                        foreach ($cart as $item) {
                            $cartCount += isset($item['quantity']) ? (int)$item['quantity'] : (isset($item->quantity) ? (int)$item->quantity : 1);
                        }
                    } elseif (is_object($cart) && isset($cart->items) && is_array($cart->items)) {
                        foreach ($cart->items as $item) {
                            $cartCount += isset($item['quantity']) ? (int)$item['quantity'] : (isset($item->quantity) ? (int)$item->quantity : 1);
                        }
                    }
                }
            @endphp

            <div>
                <a class="menu_link cart-link" href="{{ route('shopping_carts.index') }}" aria-label="Ver carrito" style="padding:0;display:flex;align-items:center">
                    <span class="cart-icon-wrap" style="position:relative;display:flex;align-items:center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" class="feather feather-shopping-cart">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span id="cart-badge" class="cart-badge" style="display:inline-block;position:absolute;top:-10px;right:-8px;background:#e11d48;color:#fff;border-radius:999px;padding:4px 8px;font-size:12px;font-weight:700;line-height:1;min-width:20px;text-align:center;box-shadow:0 1px 2px rgba(0,0,0,.2);">{{ $cartCount }}</span>
                    </span>
                </a>
            </div>
        </div>
    @endif

    <div class="menu_hamburguer" role="button" aria-label="Abrir menú" aria-expanded="false">
        <img class="menu_img" src="/assets/menu.svg" alt="">
    </div>
</nav>



<script src="/js/menu.js"></script>

<script>
    (function(){
        function setCartBadge(count){
            var el = document.getElementById('cart-badge');
            if(!el) return;
            var n = parseInt(count);
            if (isNaN(n)) n = 0;
            el.textContent = String(n);
        }
        window.addEventListener('cart:updated', function(e){
            var c = e && e.detail && (e.detail.count || e.detail.count === 0) ? e.detail.count : 0;
            setCartBadge(c);
        });
        window.updateCartBadge = setCartBadge;
    })();
</script>

<script>
    (function(){
        var toggle = document.getElementById('notif-toggle');
        var dropdown = document.getElementById('notif-dropdown');
        if(!toggle || !dropdown) return;

        toggle.addEventListener('click', function(e){
            e.stopPropagation();
            var open = dropdown.classList.toggle('open');
            dropdown.setAttribute('aria-hidden', open ? 'false' : 'true');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        // close on outside click
        document.addEventListener('click', function(){
            dropdown.classList.remove('open');
            dropdown.setAttribute('aria-hidden','true');
            toggle.setAttribute('aria-expanded','false');
        });

        dropdown.addEventListener('click', function(e){ e.stopPropagation(); });
    })();
</script>

