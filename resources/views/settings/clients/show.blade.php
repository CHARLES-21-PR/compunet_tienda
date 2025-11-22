<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 col-md-3 px-0">
                    @include('settings.nav_cate')
                </div>
                <div id="settings-main" class="col-12 col-md-9 ps-md-1">
                    <div class="bg-dark p-3" style="border-radius:14px;">
                        <h1 class="text-white">Cliente #{{ $client->id }}</h1>

                        <div class="mt-3" style="background: rgba(255,255,255,0.02); padding:12px; border-radius:8px;">
                            <p><strong class="text-white">Nombre:</strong> <span class="text-white">{{ $client->firstname }} {{ $client->lastname }}</span></p>
                            <p><strong class="text-white">Email:</strong> <span class="text-white">{{ $client->email }}</span></p>
                            <p><strong class="text-white">Registrado:</strong> <span class="text-white">{{ $client->created_at->format('Y-m-d H:i') }}</span></p>

                            <div class="mt-3">
                                <a href="{{ route('settings.clients.edit', $client) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                <a href="{{ route('settings.clients.index') }}" class="btn btn-sm btn-secondary">Volver</a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
