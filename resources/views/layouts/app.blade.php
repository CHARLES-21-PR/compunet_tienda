<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom styles (load after Tailwind/Vite so they can override) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    {{-- Load the site-wide legacy stylesheet except on auth routes (login/register/password, etc.)
         This prevents broad rules in `public/css/estilo.css` from interfering with auth layouts. --}}
    @unless(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('password.*') || request()->routeIs('verification.*'))
        <link rel="stylesheet" href="/css/estilo.css">
    @endunless
    </head>
    <body>
        <div style="display: flex; flex-direction: column; min-height: 100vh;">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-dark text-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main style="flex: 1;">
                {{-- Support both component slot and blade sections for compatibility --}}
                @isset($slot)
                    {{ $slot }}
                @endisset

                @hasSection('content')
                    @yield('content')
                @endif
            </main>
            <!-- Footer -->
            @includeIf('layouts.footer')
            
        </div>
        <!-- Global confirm modal (Alpine-based) -->
        <x-modal name="confirm-delete">
            <div class="confirm-modal p-4">
                <div class="confirm-modal-header" style="display:flex;align-items:center;gap:14px;">
                    <div class="confirm-icon" aria-hidden="true">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6v-1a3 3 0 0 1 3-3h2a3 3 0 0 1 3 3v1"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                    </div>
                    <div style="flex:1;min-width:0">
                        <h3 id="confirmDeleteTitle" class="confirm-title">Confirmar eliminación</h3>
                        <p id="confirmDeleteBody" class="confirm-body">¿Estás seguro? Esta acción no se puede deshacer.</p>
                    </div>
                </div>

                <div class="confirm-details mt-3 text-sm text-gray-600">Si eliminas este registro se eliminarán también sus datos asociados. Asegúrate de tener un respaldo si es necesario.</div>

                <div class="confirm-actions mt-6" style="display:flex;justify-content:flex-end;gap:.75rem;">
                    <button type="button" class="confirm-cancel" x-on:click="$dispatch('close-modal', 'confirm-delete')">Cancelar</button>
                    <button type="button" class="confirm-confirm" id="confirmDeleteBtn" x-on:click="(function(){ window.dispatchEvent(new CustomEvent('confirm-delete:confirmed')); })()">Eliminar</button>
                </div>
            </div>
        </x-modal>

        <script>
        // Global script: intercept forms with class 'needs-confirm' and open the Alpine modal
        (function(){
            var currentForm = null;
            var modalName = 'confirm-delete';
            var titleEl = null;
            var bodyEl = null;

            // Wait until DOM is ready so modal elements exist
            function init(){
                titleEl = document.getElementById('confirmDeleteTitle');
                bodyEl = document.getElementById('confirmDeleteBody');

                document.addEventListener('submit', function(e){
                    var form = e.target;
                    if (!form || !form.classList || !form.classList.contains('needs-confirm')) return;
                    e.preventDefault();
                    currentForm = form;
                    var title = form.getAttribute('data-confirm-title') || 'Confirmar';
                    var msg = form.getAttribute('data-confirm-message') || '¿Estás seguro?';
                    if (titleEl) titleEl.textContent = title;
                    if (bodyEl) bodyEl.textContent = msg;
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: modalName }));
                });

                window.addEventListener('confirm-delete:confirmed', function(){
                    if (!currentForm) return;
                    currentForm.classList.remove('needs-confirm');
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: modalName }));
                    setTimeout(function(){ currentForm.submit(); }, 80);
                });
            }

            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
        })();
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    </body>
</html>

