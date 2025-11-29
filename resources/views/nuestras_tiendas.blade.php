<x-app-layout>
    {{-- ========================================== --}}
    {{-- 1. BANNER PRINCIPAL (Imperial)             --}}
    {{-- ========================================== --}}
    <div class="todopoder">
        <div class="container contibannertienda">
            <div class="row">
                <div class="col-sm contetexttecnico">
                    <span class="titletecnicotra colorimpe">Tienda Principal en Imperial</span>
                    <p class="ptecnico colorprincipal">Jr. 2 de Mayo N° 475 - Imperial (a 1/2 cuadra Plaza Armas)</p>
                    
                    {{-- Mapa de Imperial --}}
                    <iframe class="mapmala" 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3893.307222373034!2d-76.3533306851817!3d-13.06456639079555!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x910ff9450c226301%3A0x627254563339000!2sJr.%202%20de%20Mayo%20475%2C%20Imperial%2015701!5e0!3m2!1ses-419!2spe!4v1677856412345!5m2!1ses-419!2spe" 
                        style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
                <div class="col-sm coltiendas colti">
                    {{-- Asegúrate de subir esta imagen a public/img --}}
                    <img src="/img/imperial.webp" class="imgtienda" alt="Tienda Imperial">
                </div>
            </div>
        </div>
        
        {{-- Animación de olas --}}
        <section>
            <div class="mar mar1"></div>
            <div class="mar mar2"></div>
            <div class="mar mar3"></div>
            <div class="mar mar4"></div>
        </section>
    </div>

    {{-- ========================================== --}}
    {{-- 2. SECCIÓN DE MARCAS                       --}}
    {{-- ========================================== --}}
    <div class="container contegenetrabajo">
        <div class="col-sm col-pc-tecnico">
            <div class="row colti">
                <h3 class="textdelogoempre">Trabajamos con:</h3>
                <div class="dropdown-divider divilog"></div>
                
                {{-- Logos de Marcas (Subir imágenes a public/img) --}}
                <div class="col-sm collogoempresas"><img src="/img/loglenovo.webp" class="imglogosempresas" alt="Lenovo"></div>
                <div class="col-sm collogoempresas"><img src="/img/loghp.webp" class="imglogosempresas" alt="HP"></div>
                <div class="col-sm collogoempresas"><img src="/img/logepson.webp" class="imglogosempresas" alt="Epson"></div>
                <div class="col-sm collogoempresas"><img src="/img/logcanon.webp" class="imglogosempresas" alt="Canon"></div>
                <div class="col-sm collogoempresas"><img src="/img/logintel.webp" class="imglogosempresas" alt="Intel"></div>
                <div class="col-sm collogoempresas"><img src="/img/logamd.webp" class="imglogosempresas" alt="AMD"></div>
            </div>
            <div class="dropdown-divider divilog"></div>
        </div>

        {{-- ========================================== --}}
        {{-- 3. OTRAS TIENDAS (San Vicente y Mala)      --}}
        {{-- ========================================== --}}
        
        {{-- Tienda San Vicente --}}
        <div class="row rowtectrabajo">
            <div class="col-sm col-pc-tecnico colti">
                <img src="/img/tiendasanvicente.webp" class="imgtiendascomer" alt="Tienda San Vicente">
            </div>
            <div class="col-sm coltexttitle colti">
                <span class="titletecnicotra">Tienda en San Vicente</span>
                <p class="ptecnico">Jr. O’Higgins N° 207 - San Vicente</p>
                
                {{-- Mapa San Vicente --}}
                <iframe class="mapmala" 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3892.837482834572!2d-76.3882914851814!3d-13.0768729907869!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x910ff94563333333%3A0x627254563339000!2sJr.%20O'Higgins%20207%2C%20San%20Vicente%20de%20Ca%C3%B1ete!5e0!3m2!1ses-419!2spe!4v1677856412345!5m2!1ses-419!2spe" 
                    style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>
        </div>

        {{-- Tienda Mala --}}
        <div class="row rowtectrabajo">
            <div class="col-sm coltexttitle colti">
                <span class="titletecnicotra colorteclap">Tienda en Mala</span>
                <p class="ptecnico">Jr. Real N ° 413 - Mala</p>
                
                {{-- Mapa Mala --}}
                <iframe class="mapmala" 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.668383834572!2d-76.6302914851814!3d-12.6568729907869!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105b1c563333333%3A0x627254563339000!2sJr.%20Real%20413%2C%20Mala!5e0!3m2!1ses-419!2spe!4v1677856412345!5m2!1ses-419!2spe" 
                    style="border:0;" allowfullscreen="" loading="lazy">
                </iframe>
            </div>
            <div class="col-sm col-pc-tecnico colti">
                <img src="/img/mala.webp" class="imgtiendascomer" alt="Tienda Mala">
            </div>
        </div>
    </div>
</x-app-layout>