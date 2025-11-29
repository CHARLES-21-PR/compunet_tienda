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
            /* Palette (only colors) - green theme */
            #auth-card { --accent-1: #16a34a; --accent-2: #10b981; --accent-3: #065f46; --card-bg: rgba(255,255,255,0.95); --muted: #4b5563; }

            /* Layout forcing (no structural changes) */
            #auth-card { max-width: 50rem !important; margin: 0 auto !important; border-radius: 1rem !important; overflow: hidden !important; }
            #auth-card .auth-row { display: flex !important; flex-direction: row !important; }
            #auth-card .auth-left, #auth-card .auth-right { width: 50% !important; }
            #auth-card .auth-left { display:flex !important; align-items:center !important; justify-content:center !important; padding:1.5rem !important; }
            #auth-card .auth-right { display:flex !important; align-items:center !important; justify-content:center !important;padding: 0 !important }

            /* Page background (light, subtle) */
            body { background: linear-gradient(180deg,#f8fafc 0%, #f3f4f6 100%) !important; }

            /* Left panel: nicer gradient and subtle overlay */
            #auth-card .auth-left {
                background: linear-gradient(135deg, var(--accent-1) 0%, var(--accent-2) 55%, var(--accent-3) 100%) !important;
                color: #ffffff !important;
            }
            #auth-card .auth-left h1 { color: rgba(255,255,255,0.98) !important; }
            #auth-card .auth-left p { color: rgba(255,255,255,0.9) !important; }

            /* Right card: lighter, crisp surface and gentle shadow */
            #auth-card .auth-right .max-w-md {
                background: var(--card-bg) !important;
                border: 1px solid rgba(15,23,42,0.04) !important;
                box-shadow: 0 10px 30px rgba(2,6,23,0.06) !important;
            }
            #auth-card .auth-right { color: #0f172a !important; }
            #auth-card .auth-right h2, #auth-card .auth-right h1 { color: #0f172a !important; }
            #auth-card .auth-right p { color: var(--muted) !important; }

            /* Inputs: ensure light background and clear text */
            #auth-card input[type="text"],
            #auth-card input[type="email"],
            #auth-card input[type="password"],
            #auth-card input[type="tel"],
            #auth-card input[type="number"],
            #auth-card textarea,
            #auth-card select {
                background-color: #ffffff !important;
                color: #0f172a !important;
                border-color: #e6e9ee !important;
            }
            #auth-card input::placeholder, #auth-card textarea::placeholder {
                color: var(--muted) !important; opacity: 1 !important;
            }

            /* Primary action buttons inside auth card */
            #auth-card button[type=submit],
            #auth-card .primary-button {
                background: linear-gradient(90deg, var(--accent-1), var(--accent-2)) !important;
                color: #ffffff !important;
                border: none !important;
                box-shadow: 0 6px 18px rgba(16,185,129,0.18) !important;
            }
            #auth-card button[type=submit]:hover, #auth-card .primary-button:hover {
                filter: brightness(.95) !important;
            }

            /* Links and small actions */
            #auth-card a { color: var(--accent-2) !important; }
            #auth-card a:hover { color: var(--accent-3) !important; }

            @media (max-width: 768px) {
                #auth-card .auth-row { flex-direction: column !important; }
                #auth-card .auth-left, #auth-card .auth-right { width: 100% !important; }
            }
        </style>
                    <!-- Right: Auth card (login / register) -->
                    <div class="w-1/2 flex items-center justify-center">
        <div class="min-h-screen flex items-center justify-center" id="auth-outer">
            <div id="auth-card" class="w-full max-w-2xl mx-auto rounded-2xl shadow-xl overflow-hidden border border-gray-100/30 bg-white/40 backdrop-blur-sm">
                <div class="flex flex-row auth-row">
                    <!-- Left: Accent panel -->
                    <div class="w-1/2 auth-left bg-gradient-to-br from-indigo-600 via-purple-600 to-fuchsia-500 p-6 flex items-center justify-center text-center text-white">
                        <div class="space-y-3 max-w-xs ">
                            <a href="/" class="inline-block justify-center items-center flex gap-2 mb-4">
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
