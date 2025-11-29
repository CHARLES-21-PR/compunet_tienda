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
                                <h1 class="text-white mb-0">Crear cliente</h1>
                                <div class="small text-white-50">Agregar un nuevo cliente</div>
                            </div>
                            <div>
                                <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-light">Volver</a>
                            </div>
                        </div>

                        <form action="{{ route('admin.clients.store') }}" method="post" class="p-2" style="background: rgba(255,255,255,0.02); border-radius:8px;">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label small">Nombre</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Email</label>
                                <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small">Contraseña</label>
                                <input type="password" name="password" class="form-control form-control-sm" required>
                            </div>

                            <div class="mt-3">
                                <button class="btn btn-sm btn-primary">Guardar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
