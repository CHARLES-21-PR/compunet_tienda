<footer>
    <div class="direccion">
        <div class="direc">
            <img src="/img/logo2.webp" alt="Logo Compunet">
        </div>
        <div class="direc-2">
            <div class="direc-1">
                <p>Local Principal Tienda Imperial</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. 2 de Mayo N° 475 - Imperial (a 1/2 cuadra Plaza Armas)</a>
                <br>
                <p>ventas:</p>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#"> 987654321</a> - <a class="num" href="#">987654321</a></p>

                <p>Local Principal Taller Imperial</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. El Carmen N° 328 - Imperial (Frente a la Plaza de Armas)</a>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">987654321</a></p>
            </div>
            
            <div class="direc-1">
                <p>Local Principal Internet Imperial</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. 2 de Mayo N° 475 (a 1/2 cuadra Plaza Armas) - Imperial</a>
                <br>
                <p>ventas:</p>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#"> 987654321</a> - <a class="num" href="#">987654321</a></p>

                <p>Local Principal Taller Imperial</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. El Carmen N° 328 - Imperial (Frente a la Plaza de Armas)</a>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">Taller: 987654321</a></p>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">Internet: 987654321</a></p>
            </div>
            
            <div class="direc-1">
                <p>Local Tienda San Vicente</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. O’Higgins N° 207 - San Vicente Ventas:</a>
                <br>
                <p>ventas:</p>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#"> 987654321</a> - <a class="num" href="#">987654321</a></p>
                <p>Local Tienda-Taller-Internet Mala</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. Real N ° 413 - Mala</a>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">Ventas: 987654321</a></p>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">Taller: 987654321</a></p>
            </div>
            
            <div class="direc-1">
                <p>Local Tienda-Taller Piura</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. Lambayeque N°400 - Chulucanas - Frente de plaza de armas</a>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">Ventas - Taller: 987654321</a></p>

                <p>Central Garantia</p>
                <a href="#"><img src="/img/m1.webp" alt="">Jr. 2 de Mayo N° 475 (a 1/2 cuadra Plaza Armas) - Imperial</a>
                <p><img src="/img/m2.webp" alt=""> <a class="num" href="#">Ventas - Taller: 987654321</a></p>
            </div>
        </div>
    </div>
    <br>
</footer>

{{-- ======================================================= --}}
{{-- BOTÓN FLOTANTE CON ALPINE.JS (Animación Suave)      --}}
{{-- ======================================================= --}}

<div x-data="{ open: false }" style="position: relative; z-index: 1000000;">
    
    {{-- 1. MENÚ DESPLEGABLE --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="card card-fijo"
         style="display: none; bottom: 80px;"> {{-- Bottom evita que tape al botón --}}
         
        <div class="card-body card-body-flotante">
            {{-- Cabecera del menú --}}
            <div class="title-card-flotante">
                <div class="row">
                    <div class="col-md-auto colphone">
                        <img class="vendeonline" src="/img/vendedoronline.webp" alt="Soporte">
                    </div>
                    <div class="col colphone">
                        <span class="span-flotante flo-ne">Atención al Cliente</span><br>
                        <span class="span-flotante flo-me">COMPUNET</span>
                    </div>
                </div>
            </div>

            {{-- Opciones del menú --}}
            <div class="alert alert-flotante alert-light" role="alert">
                <div class="row">
                    <div class="col coltexflo colphone">Aqui!! Sucursal Imperial 👋</div>
                    <div class="col-md-auto colphone">
                        <a target="_blank" href="https://api.whatsapp.com/send?phone=51926052866&text=Hola,%20Quisiera%20consultar%20sobre%20el%20producto%20en%20venta">
                            <img class="enviarflotante" src="/img/enviar.webp" alt="Enviar">
                        </a>
                    </div>
                </div>
            </div>

            <div class="alert alert-flotante alert-light" role="alert">
                <div class="row">
                    <div class="col coltexflo colphone">Aqui!! Sucursal San Vicente 👋</div>
                    <div class="col-md-auto colphone">
                        <a target="_blank" href="https://api.whatsapp.com/send?phone=51928462723&text=Hola,%20Quisiera%20consultar%20sobre%20el%20producto%20en%20venta">
                            <img class="enviarflotante" src="/img/enviar.webp" alt="Enviar">
                        </a>
                    </div>
                </div>
            </div>

            <div class="alert alert-flotante alert-light" role="alert">
                <div class="row">
                    <div class="col coltexflo colphone">Aqui!! Sucursal Mala 👋</div>
                    <div class="col-md-auto colphone">
                        <a target="_blank" href="https://api.whatsapp.com/send?phone=51900186869&text=Hola,%20Quisiera%20consultar%20sobre%20el%20producto%20en%20venta">
                            <img class="enviarflotante" src="/img/enviar.webp" alt="Enviar">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. BOTÓN FLOTANTE --}}
    <div @click="open = !open" 
         class="ico-whatsapp" 
         role="button" 
         style="cursor: pointer;">
        <img class="ico-img-wsp" src="/img/wsp.png" width="50px" height="50px" alt="WhatsApp">
    </div>

</div>