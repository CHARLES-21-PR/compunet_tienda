<x-app-layout>

@section('content')
	
	<div class="container-fluid">
		<div class="row g-0">
			<div class="col-12 col-md-3 px-0">
				@include('settings.nav_cate')
			</div>
			<div id="settings-main" class="col-12 col-md-9 ps-md-1">
				<div class="bg-dark rounded-3 p-3">
					<div class="d-flex align-items-center justify-content-between mb-3">
						<div>
							<h1 class="text-white mb-0">Categorías</h1>
							
						</div>
						<div class="d-flex align-items-center gap-2">
							<div class="input-group input-group-sm">
								<span class="input-group-text bg-transparent border-0 text-muted">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
								</span>
								
							</div>
							<a href="{{ route('settings.categories.create') }}" class="btn btn-primary btn-sm">Crear</a>
							<a href="#" class="btn btn-outline-light btn-sm">Exportar</a>
						</div>
					</div>

					@if($categories->isEmpty())
						<p class="text-white">No hay categorías.</p>
						@else
							<div class="d-flex justify-content-between align-items-center mb-2">
								<div class="settings-pagination-top w-100 d-flex justify-content-end">
									{{ $categories->links('pagination::bootstrap-5') }}
								</div>
							</div>
							<div class="table-responsive">
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
												<form action="{{ route('settings.categories.destroy', $cat) }}" method="POST" style="display:inline-block" class="needs-confirm" data-confirm-title="Eliminar categoría #{{ $cat->id }}" data-confirm-message="¿Eliminar la categoría '{{ addslashes($cat->name) }}' (ID #{{ $cat->id }})? Esta acción no se puede deshacer." data-confirm-button="Eliminar">
													@csrf
													@method('DELETE')
													<button class="btn btn-sm btn-danger">Eliminar</button>
												</form>
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
							</div>

							{{-- pagination (moved to top) --}}
					@endif
				</div>
			</div>
		</div>
	</div>
@endsection
</x-app-layout>