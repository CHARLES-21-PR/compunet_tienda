<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div>
        <header class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Iniciar sesión</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Introduce tus credenciales para acceder a tu cuenta.</p>
        </header>

        <form method="POST" action="{{ route('login') }}" class="mt-4 space-y-6 w-full">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />

                <x-text-input id="password" class="block mt-1 w-full"
                              type="password"
                              name="password"
                              required autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded  border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 " name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                </label>

                <div class="text-sm">
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <x-primary-button class="w-full">
                        {{ __('Log in') }}
                    </x-primary-button>
                </div>
            </div>
        </form>

        <div class="mt-6 text-center">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="inline-block text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200">¿No tienes cuenta? Crear una</a>
            @endif
        </div>
    </div>
</x-guest-layout>
