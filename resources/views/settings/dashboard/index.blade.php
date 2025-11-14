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
                                <h1 class="text-white mb-0">Dashboard</h1>
                               
                            </div>
                        </div>

                       

                        {{-- Categories list with products (grouped) --}}
                        <div class="mt-3">
                            <h2 class="h5 text-white mb-2">Productos por Categoría</h2>
                            <div class="row">
                                @forelse($categories as $cat)
                                    <div class="col-6 col-sm-4 col-md-3 mb-2">
                                        <div class="d-flex align-items-center justify-content-center p-3" style="background: rgba(255,255,255,0.02); border-radius:8px; min-height:72px;">
                                            <div class="text-center w-100">
                                                <div class="h3 text-white mb-0">{{ $cat->products_count ?? 0 }}</div>
                                                <div class="text-white small" style="opacity:0.85">{{ $cat->name }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="text-muted">No hay categorías o productos.</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection
</x-app-layout>           