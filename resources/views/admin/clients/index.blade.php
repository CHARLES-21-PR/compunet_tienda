<x-app-layout>
    @section('content')
        <div class="container-fluid">
            <div class="row g-0">
                <div class="col-12 px-0">
                    @include('admin.partials.nav_cate')
                </div>
                <div id="settings-main" class="col-12">
                    <div class="bg-dark p-3" style="border-radius:14px;">
                        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between mb-3 gap-3">
                            <div>
                                <h1 class="text-white mb-0">Clientes</h1>
                                <div class="small text-white-50">Gestión básica de clientes</div>
                            </div>
                            <div class="w-100 w-lg-auto d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                                <form method="get" id="clients-search-form" class="flex-grow-1">
                                    <div class="input-group" style="gap:0;">
                                        <input id="client-search" type="text" name="q" value="{{ $q ?? '' }}" class="form-control form-control-sm" placeholder="Buscar por nombre o email" style="background:#ffffff; color:#0f172a; border:1px solid rgba(0,0,0,0.08);">
                                        <button class="btn btn-sm btn-secondary" type="submit" aria-label="Buscar" title="Buscar">
                                            <span style="font-size:14px">🔍</span>
                                        </button>
                                        <button id="client-clear" type="button" class="btn btn-sm btn-outline-secondary" aria-label="Limpiar" title="Limpiar" style="background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2); color: #fff;">
                                            <span style="font-size:14px">🧹</span>
                                        </button>
                                    </div>
                                </form>
                                <a href="{{ route('admin.clients.create') }}" class="btn btn-sm btn-primary d-flex align-items-center justify-content-center" style="white-space: nowrap;">Crear cliente</a>
                            </div>
                        </div>

                        <script>
                            (function(){
                                const form = document.getElementById('clients-search-form');
                                const input = document.getElementById('client-search');
                                const clearBtn = document.getElementById('client-clear');
                                if(!clearBtn || !input) return;
                                clearBtn.addEventListener('click', function(){
                                    input.value = '';
                                    // submit the form to reload without query
                                    form.submit();
                                });
                            })();
                        </script>

                        <div style="background: rgba(255,255,255,0.02); border-radius:8px; padding:12px;">
                            <div class="table-responsive">
                                <table class="table table-dark table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombres</th>
                                            <th>Email</th>
                                            <th>Registrado</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-1">
                                                        <a href="{{ route('admin.clients.show', $user) }}" class="btn btn-sm btn-outline-light">Ver</a>
                                                        <a href="{{ route('admin.clients.edit', $user) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                                                        <form action="{{ route('admin.clients.destroy', $user) }}" method="post" style="display:inline-block" class="needs-confirm" data-confirm-title="Eliminar cliente #{{ $user->id }}" data-confirm-message="¿Eliminar al cliente '{{ addslashes($user->name) }}' (ID #{{ $user->id }})? Esta acción no se puede deshacer." data-confirm-button="Eliminar">
                                                            @csrf
                                                            @method('delete')
                                                            <button class="btn btn-sm btn-danger">Eliminar</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 d-flex justify-content-center justify-content-md-end">
                                {{ $users->withQueryString()->links() }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>
