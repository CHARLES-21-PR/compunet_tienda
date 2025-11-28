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
        <div class="top-right-actions" style="display:flex;align-items:center;padding:0 16px;gap:12px">
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
                                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                                @endif
                                @role('admin')
                                    <x-dropdown-link :href="route('settings.dashboard.index')">{{ __('Settings') }}</x-dropdown-link>
                                @endrole
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
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
                            <a href="{{ route('login') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] rounded-sm text-sm leading-normal">Register</a>
                            @endif
                        </x-slot>
                    </x-dropdown>
                @endauth
            </nav>

            {{-- Notifications (campana) --}}
            @if(isset($u->role) && $u->role === 'admin')
            
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
                                $q->where('method','yape')->whereIn('status',['pendiente','pending']);
                            })->latest()->take(6)->get();
                        }
                    }
                } catch (\Throwable $e) {
                    $pendingYape = collect();
                }
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('products') && \Illuminate\Support\Facades\Schema::hasColumn('products','stock') && \Illuminate\Support\Facades\Schema::hasColumn('products','stock_min')) {
                        $lowStock = \App\Models\Product::whereColumn('stock','<','stock_min')->latest()->take(6)->get();
                    }
                } catch (\Throwable $e) {
                    $lowStock = collect();
                }
                $notifCount = ($pendingYape ? $pendingYape->count() : 0) + ($lowStock ? $lowStock->count() : 0);
            @endphp

            <div>
                <button id="notif-toggle" class="notif-btn" aria-expanded="false" aria-label="Notificaciones">
                    <span class="notif-icon-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11c0-3.07-1.64-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.64 5.36 6 7.92 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        @if($notifCount > 0)
                            <span id="notif-badge" class="notif-badge">{{ $notifCount }}</span>
                        @endif
                    </span>
                </button>

                <div id="notif-dropdown" class="notif-dropdown" aria-hidden="true">
                    <div class="notif-dropdown-header">
                        <div class="title">Notificaciones</div>
                        <div class="count">{{ $notifCount }}</div>
                    </div>
                    <div class="notif-dropdown-body">
                        @if($pendingYape && $pendingYape->count())
                            <div class="notif-section">
                                <div class="notif-text">
                                    @foreach($pendingYape as $o)
                                        <div class="notif-item">
                                            <div class="avatar">Y</div>
                                            <div class="content">
                                                <div class="title">Pedido #{{ $o->id }}</div>
                                                <div class="meta">{{ optional($o->user)->name ?? optional($o->client)->name ?? 'Cliente' }} • {{ $o->created_at ? $o->created_at->diffForHumans() : '' }}</div>
                                            </div>
                                            <div class="actions"><a href="{{ route('settings.orders.show', ['order' => $o->id]) }}">Ver</a></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($lowStock && $lowStock->count())
                            <div class="notif-section">
                                <div class="notif-text">
                                    @foreach($lowStock as $p)
                                        <div class="notif-item">
                                            <div class="avatar">{{ strtoupper(substr($p->name ?? 'P',0,1)) }}</div>
                                            <div class="content">
                                                <div class="title">{{ $p->name ?? 'Producto' }}</div>
                                                <div class="meta">Stock: {{ $p->stock ?? 'N/A' }}</div>
                                            </div>
                                            <div class="actions"><a href="{{ route('settings.products.edit', ['product' => $p->id]) }}">Editar</a></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if((!$pendingYape || $pendingYape->isEmpty()) && (!$lowStock || $lowStock->isEmpty()))
                            <div class="notif-empty">No hay notificaciones nuevas</div>
                        @endif
                    </div>
                    <div class="notif-dropdown-footer"><a href="{{ route('settings.notifications.index') }}">Ver todas</a></div>
                </div>
            </div>

            @endif
            {{-- Cart --}}
            @php
                $cartCount = 0;
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

