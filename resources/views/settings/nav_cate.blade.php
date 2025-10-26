<h1 class="text-white text-center">Settings</h1>
<nav class="nav nav-pills flex-column flex-sm-row gap-3 mb-3 bg-dark rounded-3 pt-2 pb-2 mt-3">
  <a
    class="flex-sm-fill text-sm-center nav-link {{ request()->routeIs('settings.categories.*') ? 'active bg-white text-dark' : 'text-white' }}"
    aria-current="page"
    href="{{ route('settings.categories.index') }}">
    Categorías
  </a>

  <a
    class="flex-sm-fill text-sm-center nav-link {{ request()->routeIs('settings.products.*') ? 'active bg-white text-dark' : 'text-white' }}"
    href="{{ route('settings.products.index') }}">
    Productos
  </a>

  <a class="flex-sm-fill text-sm-center nav-link disabled text-secondary" href="#" tabindex="-1" aria-disabled="true">Orders</a>
</nav>