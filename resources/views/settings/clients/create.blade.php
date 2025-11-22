<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 col-md-3 px-0">
                    @include('settings.nav_cate')
                </div>
                <div id="settings-main" class="col-12 col-md-9 ps-md-1">
                    <div class="bg-dark p-3" style="border-radius:14px;">
                        <h1 class="text-white">Crear cliente</h1>

                        <form action="{{ route('settings.clients.store') }}" method="post">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label text-white">Nombre</label>
                                <input type="text" name="firstname" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white">Apellido</label>
                                <input type="text" name="lastname" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white">Contraseña (opcional)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-primary">Crear</button>
                                <a href="{{ route('settings.clients.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
