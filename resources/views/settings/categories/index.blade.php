<x-app-layout>

@section('content')
	
	<div class="container rounded-3 bg-dark pt-3 pb-3">
		@include('settings.nav_cate')
		<h1 class="text-white">Categorías</h1>

		<a href="{{ route('settings.categories.create') }}" class="btn btn-primary">Crear categoría</a>

		@if($categories->isEmpty())
			<p>No hay categorías.</p>
		@else
			<table class="table table-dark rounded-3">
				<thead>
					<tr>
						<th>ID</th>
						<th>Nombre</th>
						
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					@foreach($categories as $cat)
						<tr>
							<td>{{ $cat->id }}</td>
							<td>{{ $cat->name }}</td>
							
							<td>
								<a href="{{ route('settings.categories.edit', $cat) }}" class="btn btn-sm btn-secondary">Editar</a>
								<form action="{{ route('settings.categories.destroy', $cat) }}" method="POST" style="display:inline-block">
									@csrf
									@method('DELETE')
									<button class="btn btn-sm btn-danger">Eliminar</button>
								</form>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>
@endsection
</x-app-layout>