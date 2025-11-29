<form action="{{ route('settings.products.update', $product) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label for="name" class="form-label text-white">Nombre</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $product->name }}">
        <label for="category" class="form-label text-white">Categoría</label>
        <select class="form-select" id="category" name="category_id" required>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
        <label for="description" class="form-label text-white">Descripción</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ $product->description }}</textarea>
        <label for="price" class="form-label text-white">Precio</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ $product->price }}">
        <label for="stock" class="form-label text-white">Stock</label>
        <input type="number" class="form-control" id="stock" name="stock" value="{{ $product->stock }}">
        <label for="brand" class="form-label text-white">Marca</label>
        <input type="text" class="form-control" id="brand" name="brand" value="{{ $product->brand }}">
        <label for="image" class="form-label text-white">Imagen</label>
        <input type="file" class="form-control" id="image" name="image">
        <label for="status" class="form-label text-white">Estado</label>
        <select class="form-select" id="status" name="status" required>
            <option value="activo" {{ $product->status == 'activo' ? 'selected' : '' }}>Activo</option>
            <option value="inactivo" {{ $product->status == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
        </select>

    </div>
    <div class="d-flex" style="gap:8px">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <button type="button" class="btn btn-secondary" id="lowStockBackBtnInline">Volver</button>
    </div>
</form>
<script>
    (function(){
        // When the inline 'Volver' inside the form is clicked, trigger the global back handler to restore list
        document.addEventListener('click', function(e){
            const b = e.target.closest && e.target.closest('#lowStockBackBtnInline');
            if (!b) return;
            e.preventDefault();
            const globalBack = document.getElementById('lowStockBackBtn');
            if (globalBack) globalBack.click();
        });
    })();
</script>
