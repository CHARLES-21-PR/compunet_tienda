<x-app-layout>
    {{-- ========================================== --}}
    {{-- 1. BANNER PRINCIPAL (Cámaras)              --}}
    {{-- ========================================== --}}
    <div class="powercolor">
        <div class="container contibannertec">
            <div class="row">
                {{-- Texto y Llamado a la Acción --}}
                <div class="col-sm contetexttecnico">
                    <h2 class="text-tecnico">Instalación de Cámaras<br> de seguridad</h2>
                    
                    <div class="alert alert-warning dangertecnico" role="alert">
                        ¡Llámanos!
                    </div>
                    <br>
                    
                    {{-- Botón de Contacto Específico --}}
                    <a class="btn btn-danger imtecnico" target="_blank" 
                       href="https://api.whatsapp.com/send?phone=51935798472&text=Hola,%20Quisiera%20consultar%20sobre%20instalacion%20de%20camaras">
                        Encargado de cámaras: 935798472
                    </a>
                </div>

                {{-- Imagen Principal --}}
                <div class="col-sm col-img-tecnico">
                    {{-- Asegúrate de tener esta imagen --}}
                    <img src="/img/inicial.webp" class="imgslider" alt="Instalación de Cámaras">
                </div>

                {{-- Burbujas animadas --}}
                <div class="burbujas">
                    <div class="efectobur"></div><div class="efectobur"></div><div class="efectobur"></div>
                    <div class="efectobur"></div><div class="efectobur"></div><div class="efectobur"></div>
                    <div class="efectobur"></div><div class="efectobur"></div><div class="efectobur"></div>
                    <div class="efectobur"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 2. GALERÍA DE TRABAJOS REALIZADOS          --}}
    {{-- ========================================== --}}
    <section>
        <div class="app-galeria">
            <h2 class="span-precio-text text-title" style="margin-top: 2rem; margin-bottom: 2rem;">
                INSTALACIONES A NUESTROS CLIENTES
            </h2>
            
            <div class="container">
                <div class="d-flex flex-wrap justify-content-center gap-3">
                    
                    {{-- Imágenes de la Galería --}}
                    {{-- Nota: Crea la carpeta public/img/camaras y sube las fotos ahí --}}

                    <img src="/img/camaras/30.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/31.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/32.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/33.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/34.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/35.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/36.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/37.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/38.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/39.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/40.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/41.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/42.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/43.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">
                    <img src="/img/camaras/44.webp" class="img-peque" alt="Instalación Cámara" loading="lazy" style="width: 350px; height: 350px;">

                </div>
            </div>
        </div>
    </section>
</x-app-layout>