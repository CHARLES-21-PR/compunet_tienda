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
                        {!! preg_replace('/<div>\s*<p class="small text-muted">[\s\S]*?<\/p>\s*<\/div>/i', '', str_replace('<nav', '<nav style="background: rgba(255, 255, 255, 0.9); padding: .15rem .5rem; border-radius:10px; color: #0b1220; --bs-pagination-color: #0b1220; --bs-pagination-bg: transparent; --bs-pagination-border-color: rgba(0,0,0,0.06); --bs-pagination-hover-color: #064e3b; --bs-pagination-hover-bg: rgba(6,78,59,0.04); --bs-pagination-active-color: #fff; --bs-pagination-active-bg: #064e3b;"', $displayProducts->appends(request()->query())->links('pagination::bootstrap-5'))) !!}
                    @endif
                </div>
        <div class="row g-3">
            <!-- Sidebar filtros (visible solo si hay al menos 2 opciones) -->
            @if($hasFilters)
                <aside class="col-12 col-md-3">
                <form method="GET" action="{{ request()->url() }}" class="card p-3">
                    <h5 class="mb-3">Filtrar productos</h5>

                    @if($brands->count() > 1)
                        <div class="mb-3">
                            <label for="brand" class="form-label">Marca</label>
                            <select id="brand" name="brand" class="form-select">
                                <option value="">Todas</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand }}" @if($brand == $selectedBrand) selected @endif>{{ $brand }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($colors->count() > 1)
                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <select id="color" name="color" class="form-select">
                                <option value="">Todos</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color }}" @if($color == $selectedColor) selected @endif>{{ $color }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">Aplicar</button>
                        <a href="{{ request()->url() }}" class="btn btn-sm btn-outline-secondary">Borrar</a>
                    </div>
                </form>
            </aside>

            @endif

            <!-- Productos -->
            <section class="col-12 {{ $hasFilters ? 'col-md-9' : 'col-md-12' }}">

                

                @if($displayProducts->isEmpty())
                    <p class="text-black text-center">No hay productos en esta categoría con los filtros seleccionados.</p>
                @else
                    <div class="row g-3 justify-content-center">
                        @foreach($displayProducts as $product)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
                                <div class="card w-100 h-100">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                                    @else
                                        <div class="bg-secondary" style="height:180px; display:flex;align-items:center;justify-content:center;color:#fff">Sin imagen</div>
                                    @endif
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $product->name }}</h5>
                                        <p class="card-text" style="flex:1">{{ $product->description }}</p>
                                        <div class="mt-2 d-flex justify-content-between align-items-center">
                                            <strong class="text-success">s/.{{ number_format($product->price, 2) }}</strong>
                                            <a href="{{ route('products.details', $product->id) }}" class="btn btn-sm btn-primary">Comprar</a>
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