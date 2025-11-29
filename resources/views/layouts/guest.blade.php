<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        {{-- <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Emergency inline CSS to ensure auth layout renders correctly even if legacy CSS loads from elsewhere. --}}
        <style>
            /* Strong, scoped rules for the auth layout (temporary, reversible) */
            #auth-card { max-width: 50rem !important; margin: 0 auto !important; border-radius: 1rem !important; overflow: hidden !important; }
            #auth-card .auth-row { display: flex !important; flex-direction: row !important; }
            #auth-card .auth-left, #auth-card .auth-right { width: 50% !important; }
            #auth-card .auth-left { display:flex !important; align-items:center !important; justify-content:center !important; padding:1.5rem !important; }
            #auth-card .auth-right { display:flex !important; align-items:center !important; justify-content:center !important; padding:1.5rem !important; }
            @media (max-width: 768px) {
                #auth-card .auth-row { flex-direction: column !important; }
                #auth-card .auth-left, #auth-card .auth-right { width: 100% !important; }
            }
        </style>
                    <!-- Right: Auth card (login / register) -->
                    <div class="w-1/2 p-6 flex items-center justify-center">
        <div class="min-h-screen flex items-center justify-center p-6" id="auth-outer">
            <div id="auth-card" class="w-full max-w-2xl mx-auto rounded-2xl shadow-xl overflow-hidden border border-gray-100/30 bg-white/40 backdrop-blur-sm">
                <div class="flex flex-row auth-row">
                    <!-- Left: Accent panel -->
                    <div class="w-1/2 auth-left bg-gradient-to-br from-indigo-600 via-purple-600 to-fuchsia-500 p-6 flex items-center justify-center text-center text-white">
                        <div class="space-y-3 max-w-xs">
                            <a href="/" class="inline-block">
                                <x-application-logo class="w-20 h-20" />
                            </a>
                            <h1 class="text-xl font-semibold">{{ config('app.name', 'Laravel') }}</h1>
                            <p class="text-sm opacity-90">Bienvenido — administra tu cuenta o crea una nueva.</p>
                        </div>
                    </div>

                    <!-- Right: Auth card (login / register) -->
                    <div class="w-1/2 auth-right p-6 flex items-center justify-center">
                        <div class="w-full max-w-md bg-white rounded-lg shadow-md p-6">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
