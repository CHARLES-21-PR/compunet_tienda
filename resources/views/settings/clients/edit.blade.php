<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 col-md-3 px-0">
                    @include('settings.nav_cate')
                </div>
                <div id="settings-main" class="col-12 col-md-9 ps-md-1">
                    <div class="bg-dark p-3" style="border-radius:14px;">
                        <h1 class="text-white">Editar cliente</h1>

                        <form action="{{ route('settings.clients.update', $client) }}" method="post">
                            @csrf
                            @method('put')
                            <div class="mb-2">
                                <label class="form-label text-white">Nombres y apellidos</label>
                                <input type="text" name="name" class="form-control" value="{{ $client->name }}" required>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label text-white">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $client->email }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-white">Contraseña (dejar en blanco para mantener)</label>
                                <input type="password" name="password" class="form-control">
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-primary">Guardar</button>
                                <a href="{{ route('settings.clients.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
