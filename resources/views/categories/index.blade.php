<x-app-layout>
    @section('content')
    <div class="container py-4">
        <h1 class="h3 mb-3">Categorías</h1>
        @if($categories->isEmpty())
            <div class="alert alert-secondary">No hay categorías disponibles.</div>
        @else
            <div class="row g-3">
                @foreach($categories as $category)
                    <div class="col-6 col-md-4 col-lg-3">
                        <a href="{{ route('categories.show', $category->id ?? $category->slug ?? $category->name) }}" class="text-decoration-none">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body p-3">
                                    <h5 class="card-title mb-0">{{ $category->name }}</h5>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    @endsection
</x-app-layout>
