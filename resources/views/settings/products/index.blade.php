
<x-app-layout>
    @section('content')
        <div class="container rounded-3 bg-dark pt-3 pb-3">
            @include('settings.nav_cate')
            <h1 class="text-white">Productos</h1>

            <a href="{{ route('settings.products.create') }}" class="btn btn-primary mb-3">Crear producto</a>

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