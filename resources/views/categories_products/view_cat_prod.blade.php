<x-app-layout>

    <h1 class="text-black p-3 text-center">{{ $category->name }}</h1>

    @if($category->products->isEmpty())
        <p class="text-black text-center">No hay productos en esta categoría.</p>
    @else
        <div class="container">
            <div class="row g-3 justify-content-center">
                @foreach($category->products as $product)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex">
                        <div class="card w-100 h-100">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}">
                            @else
                                <div class="bg-secondary" style="height:180px; display:flex;align-items:center;justify-content:center;color:#fff">Sin imagen</div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text" style="flex:1">{{ $product->description }}</p>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <strong class="text-success">s/.{{ number_format($product->price, 2) }}</strong>
                                    <a href="{{ route('products.details', $product->id) }}" class="btn btn-sm btn-primary">Comprar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    
</x-app-layout>