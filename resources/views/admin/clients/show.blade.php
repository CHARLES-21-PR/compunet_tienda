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
                                <h1 class="text-white mb-0">Cliente: {{ $user->name }}</h1>
                                <div class="small text-white-50">Detalles del cliente</div>
                            </div>
                            <div>
                                <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-outline-light">Volver</a>
                            </div>
                        </div>

                        <div style="background: rgba(255,255,255,0.02); border-radius:8px; padding:12px;">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">ID</dt>
                                <dd class="col-sm-9">{{ $user->id }}</dd>

                                <dt class="col-sm-3">Nombre</dt>
                                <dd class="col-sm-9">{{ $user->name }}</dd>

                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">{{ $user->email }}</dd>

                                <dt class="col-sm-3">Creado</dt>
                                <dd class="col-sm-9">{{ $user->created_at->format('Y-m-d H:i') }}</dd>
                            </dl>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
