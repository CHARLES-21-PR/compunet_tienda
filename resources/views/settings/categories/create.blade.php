<x-app-layout>
	@section('content')
		<div class="container">
            @include('settings.nav_cate')
			<h1 class="text-white">Crear Categoría</h1>

			<form action="{{ route('settings.categories.store') }}" method="POST">
				@csrf
				<div class="mb-3">
					<label for="name" class="form-label text-white">Nombre</label>
					<input type="text" class="form-control" id="name" name="name" required>
				</div>
				<button type="submit" class="btn btn-primary">Crear</button>
                <a href="{{ route('settings.categories.index') }}" class="btn btn-secondary">Cancelar</a>
			</form>
		</div>
	@endsection
</x-app-layout>
