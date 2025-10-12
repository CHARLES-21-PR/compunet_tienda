<header class="header-top">
        <div class="redes">

           <div class="redes-var">
               <img class="ico" src="/img/ins.webp" alt="" srcset="">
               <img class="ico" src="/img/face.webp" alt="" srcset="">
           </div>
           <div class="redes-1">
             <a href="">Inicio</a>
             <a href="">Nuestras tiendas</a>
             <a href="">Contáctanos</a>
           </div>

        </div>
</header>
<nav>
           <div class="logo">
            <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
               
           </div>

           <div class="nav-1">

            <div class="enlace enlace-show">

                <a class="menu_link" href=""><img class="icon1" src="/img/l1.webp" alt="">Equipos de computo<img class="arrow" src="assets/arrow.svg" alt=""></a>

                
                <ul class="menu_nesting">
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a1.webp" alt="">Computadoras</a>
                    </li>
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a2.webp" alt="">Laptops</a>
                    </li>
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a3.webp" alt="">Tablets</a>
                    </li>
                </ul>
            </div>
            <div class="enlace ">
                <a class="menu_link" href=""><img class="icon1" src="/img/l2.webp" alt="">Impresoras</a> 
            </div>
            <div class="enlace enlace-show">
                <a class="menu_link" href=""><img class="icon1" src="/img/l3.webp" alt="">Catálogos<img class="arrow" src="assets/arrow.svg" alt=""></a>
                <ul class="menu_nesting">
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a4.webp" alt="">Tintas</a>
                    </li>
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a5.webp" alt="">SSD</a>
                    </li>
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a6.webp" alt="">COMBO GAMER</a>
                    </li>
                </ul>
            </div>
            <div class="enlace ">
                <a class="menu_link" href=""><img class="icon1" src="/img/l4.webp" alt="">Internet ilimitado</a>
            </div>
            <div class="enlace enlace-show">
                <a class="menu_link" href=""><img class="icon1" src="/img/l5.webp" alt="">Atención especializada<img class="arrow" src="assets/arrow.svg" alt=""></a>
                <ul class="menu_nesting">
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a7.webp" alt="">Camara de vigilancia</a>
                    </li>
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a8.webp" alt="">Soporte técnico</a>
                    </li>
                    <li class="menu_inside">
                        <a href="#" class="menu_link menu_link--inside"><img class="icon2" src="/img/a9.webp" alt="">Nuestros clientes</a>
                    </li>
                </ul>
            </div>
                         
                
             </div>
             @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                    
                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md   focus:outline-none transition ease-in-out duration-150">
                                            <div>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                                <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                                </svg>
                                                {{-- {{ Auth::user()->name }} --}}
                                            </div>

                                            <div class="ms-1">
                                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')">
                                            {{ __('Profile') }}
                                        </x-dropdown-link>

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf

                                            <x-dropdown-link :href="route('logout')"
                                                    onclick="event.preventDefault();
                                                                this.closest('form').submit();">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                        </div>

                        @else

                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md   focus:outline-none transition ease-in-out duration-150">
                                    <div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                                        <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                                        </svg>
                                        {{-- {{ Auth::user()->name }} --}}
                                    </div>

                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <a
                                    href="{{ route('login') }}"
                                    class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"
                                >
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a
                                        href="{{ route('register') }}"
                                        class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                                        Register
                                    </a>
                                @endif
                            </x-slot>
                        </x-dropdown>
                            

                        
                    @endauth
                </nav>
            @endif

             
            <div class="menu_hamburguer">
                <img class="menu_img" src="/assets/menu.svg" alt="">
             </div>
       </nav>

