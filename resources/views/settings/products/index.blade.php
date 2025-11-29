
<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 col-md-3 px-0">
                    @include('settings.nav_cate')
                </div>
                    <div id="settings-main" class="col-12 col-md-9 ps-md-1">
                        <div class="bg-dark p-3" style="border-radius:14px;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h1 class="text-white mb-0">Productos</h1>
                                <div class="text-muted small">Administra tus productos — búsqueda rápida y acciones</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                    <form id="productsFilterForm" method="GET" action="{{ route('settings.products.index') }}" class="d-flex align-items-center gap-2">
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
                                        <a href="{{ route('settings.products.index') }}" class="btn btn-outline-light btn-sm">Limpiar</a>
                                        <a href="#" class="btn btn-outline-light btn-sm">Exportar</a>
                                        <a href="{{ route('settings.products.create') }}" class="btn btn-primary btn-sm">Crear</a>
                                    </form>
                            </div>
                        </div>

            @if($products->isEmpty())
                <p>No hay productos.</p>
            @else
                <table class="table table-dark rounded-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
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
                                <td>${{ number_format($prod->price, 2) }}</td>
                                <td>{{ $prod->stock }}</td>
                                <td>
                                    <a href="{{ route('settings.products.edit', $prod) }}" class="btn btn-sm btn-secondary">Editar</a>
                                    <form action="{{ route('settings.products.destroy', $prod) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endsection
</x-app-layout>