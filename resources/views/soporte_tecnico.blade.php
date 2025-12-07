<x-app-layout>
    {{-- ========================================== --}}
    {{-- 1. BANNER DE SOPORTE (Fondo degradado)     --}}
    {{-- ========================================== --}}
    <div class="powercolor">
        {{-- Burbujas animadas (Movidas fuera del container para ocupar todo el banner) --}}
        <div class="burbujas">
            <div class="efectobur"></div><div class="efectobur"></div><div class="efectobur"></div>
            <div class="efectobur"></div><div class="efectobur"></div><div class="efectobur"></div>
            <div class="efectobur"></div><div class="efectobur"></div><div class="efectobur"></div>
            <div class="efectobur"></div>
        </div>

        <div class="container contibannertec" style="position: relative; z-index: 2;">
            <div class="row">
                {{-- Texto y Botones de Contacto --}}
                <div class="col-sm contetexttecnico">
                    <h2 class="text-tecnico">¿Necesitas ayuda <br> con tu PC?</h2>
                    
                    <div class="alert alert-warning dangertecnico" role="alert">
                        ¡Llámanos ya!
                    </div>
                    <br>
                    
                    {{-- Botones de WhatsApp por sede --}}
                    <a class="btn btn-danger imtecnico" target="_blank" 
                       href="https://api.whatsapp.com/send?phone=51900937418&text=Hola,%20Quisiera%20consultar%20sobre%20un%20servicio%20técnico">
                        Imperial: 900937418
                    </a>
                    
                    <a class="btn btn-danger svtecnico" target="_blank" 
                       href="https://api.whatsapp.com/send?phone=51921304402&text=Hola,%20Quisiera%20consultar%20sobre%20un%20servicio%20técnico">
                        San Vicente: 921304402
                    </a>
                    
                    <a class="btn btn-danger malatecnico" target="_blank" 
                       href="https://api.whatsapp.com/send?phone=51928914095&text=Hola,%20Quisiera%20consultar%20sobre%20un%20servicio%20técnico">
                        Mala: 928914095
                    </a>
                </div>

                {{-- Imagen Principal (Técnico) --}}
                <div class="col-sm col-img-tecnico">
                    <img src="/img/soporte.webp" class="imgslider" alt="Soporte Técnico">
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. LISTA DE SERVICIOS                      --}}
    {{-- ========================================== --}}
    <div class="container contegenetrabajo">
        
        {{-- Computadoras --}}
        <div class="row rowtectrabajo">
            <div class="col-sm col-pc-tecnico">
                <img src="/img/tec1.webp" class="imgtecnicotrabajo" alt="Reparación PC">
            </div>
            <div class="col-sm coltexttitle">
                <span class="titletecnicotra">Soporte técnico de Computadoras</span>
                <p class="ptecnico">
                    Mantenimiento Preventivo - Mantenimiento Correctivo - Repuesto de PC - Formateo - Antivirus <br>
                    También podemos repotenciar tu PC.
                </p>
            </div>
        </div>

        {{-- Laptops --}}
        <div class="row rowtectrabajo">
            <div class="col-sm coltexttitle">
                <span class="titletecnicotra colorteclap">Soporte técnico de Laptops</span>
                <p class="ptecnico">
                    Mantenimiento Preventivo - Mantenimiento Correctivo - Repuesto para laptops - Formateo - Antivirus - 
                    Reparación de placas - Cambio de Pantalla - Reparación de Bisagra - Cambio de teclado<br>
                    También podemos repotenciar tu laptop.
                </p>
            </div>
            <div class="col-sm col-pc-tecnico">
                <img src="/img/tec2.webp" class="imgtecnicotrabajo" alt="Reparación Laptop">
            </div>
        </div>

        {{-- Impresoras --}}
        <div class="row rowtectrabajo">
            <div class="col-sm col-pc-tecnico">
                <img src="/img/tec3.webp" class="imgtecnicotrabajo" alt="Reparación Impresoras">
            </div>
            <div class="col-sm coltexttitle">
                <span class="titletecnicotra colortecimp">Soporte técnico de Impresoras</span>
                <p class="ptecnico">
                    Mantenimiento Preventivo - Mantenimiento Correctivo - Repuesto de Impresoras - Instalación de Sistema Continuo - 
                    Reset de Contador - Reparación de Impresoras Matriciales - Láser - A3 - A4 - Ticketera<br>
                    Podemos ayudarte con las diferentes marcas de Impresoras.
                </p>
            </div>
        </div>

    </div>
</x-app-layout>