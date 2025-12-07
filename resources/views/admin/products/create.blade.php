<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 px-0">
                    @include('admin.partials.nav_cate')
                </div>
                    <div id="settings-main" class="col-12">
                        <div class="bg-dark rounded-3 p-3">
                        <h1 class="text-white">Crear Producto</h1>

                        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label text-white">Nombre</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label text-white">Categoría</label>
                                    <select class="form-select" id="category" name="category_id" required>
                                        <option value="" disabled selected>Selecciona una categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label text-white">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label text-white">Precio</label>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="stock" class="form-label text-white">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="brand" class="form-label text-white">Marca</label>
                                    <input type="text" class="form-control" id="brand" name="brand">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label text-white">Imagen</label>
                                    <input type="file" class="form-control" id="image" name="image">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label text-white">Estado</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="" disabled selected>Selecciona un estado</option>
                                        <option value="activo">Activo</option>
                                        <option value="inactivo">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Crear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
