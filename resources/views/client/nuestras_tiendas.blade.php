<x-app-layout>
    {{-- Banner Principal con Mapa y Animación de Olas --}}
    <div class="todopoder">
        <div class="container contibannertienda">
            <div class="row">
                <div class="col-sm contetexttecnico">
                    <span class="titletecnicotra colorimpe">Tienda Principal en Imperial</span>
                    <p class="ptecnico colorprincipal">Jr. 2 de Mayo N° 475 - Imperial (a 1/2 cuadra Plaza Armas)</p>
                    {{-- Mapa iframe --}}
                    <iframe class="mapmala" src="https://maps.google.com/maps?q=-13.061265,-76.353328&z=15&output=embed" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
                <div class="col-sm coltiendas colti">
                    {{-- Asegúrate de tener esta imagen en public/img --}}
                    <img src="/img/imperial.webp" class="imgtienda" alt="Tienda Imperial">
                </div>
            </div>
        </div>
        
        {{-- Animación de olas (CSS .mar) --}}
        <section>
            <div class="mar mar1"></div>
            <div class="mar mar2"></div>
            <div class="mar mar3"></div>
            <div class="mar mar4"></div>
        </section>
    </div>

    {{-- Sección de Marcas y Otras Tiendas --}}
    <div class="container contegenetrabajo">
        <div class="col-sm col-pc-tecnico">
            {{-- Logos de marcas --}}
            <div class="row colti">
                <h3 class="textdelogoempre">Trabajamos con:</h3>
                <div class="dropdown-divider divilog"></div>
                <div class="col-sm collogoempresas"><img src="/img/loglenovo.webp" class="imglogosempresas" alt="Lenovo"></div>
                <div class="col-sm collogoempresas"><img src="/img/loghp.webp" class="imglogosempresas" alt="HP"></div>
                <div class="col-sm collogoempresas"><img src="/img/logepson.webp" class="imglogosempresas" alt="Epson"></div>
                <div class="col-sm collogoempresas"><img src="/img/logcanon.webp" class="imglogosempresas" alt="Canon"></div>
                <div class="col-sm collogoempresas"><img src="/img/logintel.webp" class="imglogosempresas" alt="Intel"></div>
                <div class="col-sm collogoempresas"><img src="/img/logamd.webp" class="imglogosempresas" alt="AMD"></div>
            </div>
            <div class="dropdown-divider divilog"></div>
        </div>

        {{-- Tienda San Vicente --}}
        <div class="row rowtectrabajo">
            <div class="col-sm col-pc-tecnico colti">
                <img src="/img/tiendasanvicente.webp" class="imgtiendascomer" alt="Tienda San Vicente">
            </div>
            <div class="col-sm coltexttitle colti">
                <span class="titletecnicotra">Tienda en San Vicente</span>
                <p class="ptecnico">Jr. O’Higgins N° 207 - San Vicente</p>
                <iframe class="mapmala" src="https://maps.google.com/maps?q=-13.076321,-76.386683&z=15&output=embed" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>

        {{-- Tienda Mala --}}
        <div class="row rowtectrabajo">
            <div class="col-sm coltexttitle colti">
                <span class="titletecnicotra colorteclap">Tienda en Mala</span>
                <p class="ptecnico">Jr. Real N ° 413 - Mala</p>
                <iframe class="mapmala" src="https://maps.google.com/maps?q=-12.658645,-76.630456&z=15&output=embed" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
            <div class="col-sm col-pc-tecnico colti">
                <img src="/img/mala.webp" class="imgtiendascomer" alt="Tienda Mala">
            </div>
        </div>
    </div>
</x-app-layout>
