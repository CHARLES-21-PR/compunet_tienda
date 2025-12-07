<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 px-0">
                    @include('admin.partials.nav_cate')
                </div>
                    <div id="settings-main" class="col-12">
                        <div class="bg-dark rounded-3 p-3">
                        <h1 class="text-white">Editar Categoría</h1>
                        <form action="{{ route('settings.categories.update', $category) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="name" class="form-label text-white">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ $category->name }}" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                            <a href="{{ route('settings.categories.index') }}" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
