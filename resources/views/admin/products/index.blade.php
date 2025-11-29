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
                                <h1 class="text-white mb-0">Productos</h1>
                                
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                    <form id="productsFilterForm" method="GET" action="{{ route('admin.products.index') }}" class="d-flex align-items-center gap-2">
                                        <div class="search-input-wrapper" style="position: relative; width: 420px;">
                                            <input id="productsSearchInput" type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm text-dark search-with-icon" placeholder="Buscar producto..." style="background: rgba(255,255,255,0.95); padding-left: .75rem; padding-right: 2.2rem; width:100%; height:40px;">
                                            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); color: #0f172a; pointer-events: none;"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                        </div>
                                        <select id="productsCategorySelect" name="category" class="form-select form-select-sm" style="height:40px; min-width: 180px;">
                                            <option value="">Todas las categorías</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" @if(request('category') == $cat->id) selected @endif>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-light btn-sm">Limpiar</a>
                                        <a href="#" class="btn btn-outline-light btn-sm">Exportar</a>
                                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">Crear</a>
                                    </form>
                            </div>
                        </div>

                        @if($products->isEmpty())
                            <p class="text-white">No hay productos.</p>
                        @else
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="settings-pagination-top w-100 d-flex justify-content-end">
                                    {!! str_replace('<nav', '<nav style="background: rgba(33, 37, 41, 0.75); padding: .15rem .5rem; border-radius:10px; color: #fff; --bs-pagination-color: #fff; --bs-pagination-bg: transparent; --bs-pagination-border-color: rgba(255,255,255,0.06); --bs-pagination-hover-color: #fff; --bs-pagination-hover-bg: rgba(255,255,255,0.04); --bs-pagination-active-color: #0f172a; --bs-pagination-active-bg: #eef2ff;"', $products->links('pagination::bootstrap-5')) !!}
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-dark rounded-3">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Categoría</th>
                                            <th>Precio</th>
                                            <th>Descripción</th>
                                            <th>Stock</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $prod)
                                            <tr>
                                                <td>{{ $prod->id }}</td>
                                                <td>{{ $prod->name }}</td>
                                                <td>{{ $prod->category->name ?? '-' }}</td>
                                                <td>s/.{{ number_format($prod->price, 2) }}</td>
                                                <td class="product-desc" title="{{ $prod->description }}">{{ \Illuminate\Support\Str::limit(strip_tags($prod->description), 100) }}</td>
                                                <td>{{ $prod->stock }}</td>
                                                <td>
                                                    <a href="{{ route('admin.products.edit', $prod) }}" class="btn btn-sm btn-secondary">Editar</a>
                                                    <form action="{{ route('admin.products.destroy', $prod) }}" method="POST" style="display:inline-block" class="needs-confirm" data-confirm-title="Eliminar producto #{{ $prod->id }}" data-confirm-message="¿Eliminar el producto '{{ addslashes($prod->name) }}' (ID #{{ $prod->id }})? Esta acción no se puede deshacer." data-confirm-button="Eliminar">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger">Eliminar</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{-- pagination (moved to top) --}}
                            
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>

<style>
.product-desc{
    max-width:320px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const form = document.getElementById('productsFilterForm');
    if(!form) return;
    const input = document.getElementById('productsSearchInput');
    const select = document.getElementById('productsCategorySelect');

    // submit when category changes
    if(select){
        select.addEventListener('change', function(){
            form.submit();
        });
    }

    // submit on Enter in search input
    if(input){
        input.addEventListener('keydown', function(e){
            if(e.key === 'Enter'){
                e.preventDefault();
                form.submit();
            }
        });
    }
});
</script>
