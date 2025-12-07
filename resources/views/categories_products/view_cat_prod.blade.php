<x-app-layout>

    <h1 class="text-black p-3 text-center">{{ $category->name }}</h1>

    @php
        // Prepare products collection and filters from GET params
        $products = $category->products;
        $selectedBrand = request()->get('brand');
        $selectedColor = request()->get('color');

        if ($selectedBrand) {
            $products = $products->where('brand', $selectedBrand);
        }
        if ($selectedColor) {
            $products = $products->where('color', $selectedColor);
        }

        // Unique filter options
        $brands = $category->products->pluck('brand')->filter()->unique()->values();
        $colors = $category->products->pluck('color')->filter()->unique()->values();
        $hasFilters = ($brands->count() > 1) || ($colors->count() > 1);
    @endphp

    @php
        // Prepare a paginator for the products list if it's not already paginated.
        if (method_exists($products, 'links')) {
            $displayProducts = $products;
        } else {
            $page = request()->get('page', 1);
            $perPage = 12; // ajustar por página
            $items = collect($products);
            $total = $items->count();
            $paged = $items->forPage($page, $perPage)->values();
            $displayProducts = new \Illuminate\Pagination\LengthAwarePaginator($paged, $total, $perPage, $page, [
                'path' => request()->url(),
                'query' => request()->query(),
            ]);
        }
    @endphp

    <div class="container">
        <div class="d-flex justify-content-end mb-3">
                    @if(method_exists($displayProducts, 'links'))
                        {!! preg_replace('/<div>\s*<p class="small text-muted">[\s\S]*?<\/p>\s*<\/div>/i', '', str_replace('<nav', '<nav class="custom-pagination"', $displayProducts->appends(request()->query())->links('pagination::bootstrap-5'))) !!}
                    @endif
                </div>
        <div class="row g-3">
            <!-- Sidebar filtros (visible solo si hay al menos 2 opciones) -->
            @if($hasFilters)
                <aside class="col-12 col-md-3">
                    <button class="btn btn-primary w-100 d-md-none mb-3 d-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-funnel-fill" viewBox="0 0 16 16">
                            <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z"/>
                        </svg>
                        Filtrar productos
                    </button>
                    <div class="collapse d-md-block" id="filterCollapse">
                        <form method="GET" action="{{ request()->url() }}" class="card p-3 shadow-sm border-0">
                            <h5 class="mb-3 fw-bold">Filtrar productos</h5>

                            @if($brands->count() > 1)
                                <div class="mb-3">
                                    <label for="brand" class="form-label small fw-bold text-muted">MARCA</label>
                                    <select id="brand" name="brand" class="form-select form-select-sm">
                                        <option value="">Todas</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand }}" @if($brand == $selectedBrand) selected @endif>{{ $brand }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($colors->count() > 1)
                                <div class="mb-3">
                                    <label for="color" class="form-label small fw-bold text-muted">COLOR</label>
                                    <select id="color" name="color" class="form-select form-select-sm">
                                        <option value="">Todos</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color }}" @if($color == $selectedColor) selected @endif>{{ $color }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">Aplicar filtros</button>
                                <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </aside>
            @endif

            <!-- Productos -->
            <section class="col-12 {{ $hasFilters ? 'col-md-9' : 'col-md-12' }}">

                

                @if($displayProducts->isEmpty())
                    <p class="text-black text-center">No hay productos en esta categoría con los filtros seleccionados.</p>
                @else
                    <div class="row g-3 justify-content-center">
                        @foreach($displayProducts as $product)
                            <div class="col-6 col-sm-6 col-md-4 col-lg-3 d-flex">
                                <div class="card w-100 h-100 shadow-sm border-0 product-card">
                                    <div class="position-relative">
                                        @if($product->image)
                                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                                        @else
                                            <div class="no-image-placeholder">Sin imagen</div>
                                        @endif
                                    </div>
                                    <div class="card-body d-flex flex-column p-3">
                                        <h5 class="card-title small fw-bold mb-2 text-truncate">{{ $product->name }}</h5>
                                        <p class="card-text small text-muted mb-3 d-none d-sm-block" style="flex:1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $product->description }}</p>
                                        <div class="mt-auto d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                                            <strong class="text-success">s/.{{ number_format($product->price, 2) }}</strong>
                                            <a href="{{ route('products.details', $product->id) }}" class="btn btn-sm btn-primary w-100 w-sm-auto">
                                                <span class="d-none d-sm-inline">Comprar</span>
                                                <span class="d-inline d-sm-none"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart-plus" viewBox="0 0 16 16"><path d="M9 5.5a.5.5 0 0 0-1 0V7H6.5a.5.5 0 0 0 0 1H8v1.5a.5.5 0 0 0 1 0V8h1.5a.5.5 0 0 0 0-1H9V5.5z"/><path d="M.5 1a.5.5 0 0 0 0 1h1.11l.401 1.607 1.498 7.985A.5.5 0 0 0 4 12h1a2 2 0 1 0 0 4 2 2 0 0 0 0-4h7a2 2 0 1 0 0 4 2 2 0 0 0 0-4h1a.5.5 0 0 0 .491-.408l1.5-8A.5.5 0 0 0 14.5 3H2.89l-.405-1.621A.5.5 0 0 0 2 1H.5zm3.915 10L3.102 4h10.796l-1.313 7h-8.17zM6 14a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm7 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
            
        </div>
    </div>
    
    
</x-app-layout>