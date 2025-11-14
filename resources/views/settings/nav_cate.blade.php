<!-- Modern sidebar: Offcanvas on mobile + collapsible panel on desktop -->
<style>
  /* Sidebar panel for desktop: inline panel (pegado a la izquierda) */
  #settingsSidebarPanel {
    z-index: 999; /* above content */
    transition: right .18s ease, transform .18s ease, width .22s ease;
    /* keep the sidebar full-height like before and stuck to the viewport */
    position: sticky;
    top: 1rem;
    height: calc(100vh - 2rem);
    width: 240px; /* aumentar ligeramente para más separación */
    overflow: visible; /* allow the toggle to stick out */
    padding-bottom: 1rem;
  }

  /* Toggle button: inside when expanded, sticks out when collapsed */
  .sidebar-toggle {
    position: absolute;
    right: 12px; /* inside the panel when expanded */
    top: 50%;
    transform: translateY(-50%);
    width: 34px;
    height: 34px;
    padding: 0.12rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 6px 14px rgba(0,0,0,0.12);
    background: #fff;
    color: #222;
    font-size: 0.9rem;
  }

  /* avatar / admin icon */
  .sidebar-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #6c757d;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    margin-right: .5rem;
  }

  /* ensure header area is a positioned container so avatar and toggle can be absolutely placed */
  #settingsSidebarPanel .d-flex.position-relative { position: relative; min-height: 56px; }

  /* When collapsed: center the avatar inside the small panel (symmetrical with toggle) */
  #settingsSidebarPanel.sidebar-collapsed .sidebar-avatar {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    margin: 0;
    z-index: 1000;
  }

  /* When expanded: avatar returns to normal flow at left */
  #settingsSidebarPanel:not(.sidebar-collapsed) .sidebar-avatar {
    position: static;
    transform: none;
    margin-right: .5rem;
  }

  #settingsSidebarPanel.sidebar-collapsed {
    width: 64px;
  }

  /* when collapsed, nudge the toggle outside the panel so it remains visible */
  #settingsSidebarPanel.sidebar-collapsed .sidebar-toggle,
  #settingsSidebarPanel.sidebar-collapsed .ms-auto > .sidebar-toggle {
    /* push the toggle further outside so it stays fully visible when collapsed */
    right: -56px; /* fully outside the collapsed 64px panel */
    box-shadow: 0 6px 18px rgba(0,0,0,0.18);
    z-index: 22000; /* ensure it sits above everything */
    transform: translateY(-50%) rotate(0deg);
    pointer-events: auto;
  }

  /* Center icons and make links compact when collapsed */
  #settingsSidebarPanel.sidebar-collapsed .nav-link {
    justify-content: center !important;
    padding-left: .35rem !important;
    padding-right: .35rem !important;
  }
  /* ensure the svg/icon inside the link is centered and doesn't keep right margin */
  #settingsSidebarPanel .nav-link .me-2 {
    margin-right: 0 !important;
    margin-left: 0 !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
  }
  /* hide labels when collapsed (already present), ensure icons occupy center */
  #settingsSidebarPanel.sidebar-collapsed .label { display: none !important; }

  /* Ensure the column that contains the sidebar allows overflow so the toggle can stick out.
     Also lock the md+ column width to match the panel width so there is no empty gap between
     the (smaller) panel and the grid column that contains it. The collapsed body class
     still overrides the width to 64px when collapsed. */
  .container-fluid > .row > .col-12.col-md-3,
  .container-fluid > .row > .col-md-3 { overflow: visible !important; }

  @media (min-width: 768px) {
    .container-fluid > .row > .col-12.col-md-3,
    .container-fluid > .row > .col-md-3 {
      flex: 0 0 240px !important;
      max-width: 240px !important;
    }
    /* Make the right column flexible instead of forcing a calc() width.
       Using `min-width: 0` allows flex items to shrink properly and avoids
       layout breakage around bootstrap container breakpoint (xxl ~1320px).
       Let the right column grow/shrink naturally. */
    .container-fluid > .row > .col-12.col-md-9,
    .container-fluid > .row > .col-md-9 {
      flex: 1 1 0% !important;
      max-width: none !important;
      min-width: 0 !important; /* important for overflowing children like wide tables */
    }
  }

  /* Allow tables and wide content inside #settings-main to scroll instead of
     forcing the layout to overflow the viewport at awkward breakpoints. */
  #settings-main { min-width: 0; }
  #settings-main .table-responsive, #settings-main .table-wrapper { overflow-x: auto; }
  #settings-main table { min-width: 100%; }

  /* Modernizar apariencia del área CRUD dentro de #settings-main */
  #settings-main .bg-dark {
    /* tonalidad oscura con sutil degradado, borde ligero y sombra suave */
    background: linear-gradient(180deg, rgba(23,27,33,0.96), rgba(12,14,18,0.96)) !important;
    border: 1px solid rgba(255,255,255,0.03) !important;
    box-shadow: 0 6px 18px rgba(2,6,23,0.45);
    border-radius: 14px !important;
    padding: 1.25rem !important;
  }

  /* Titulares más limpios */
  #settings-main h1, #settings-main h2, #settings-main h3 {
    color: #f8fafc;
    font-weight: 700;
    margin-bottom: .5rem;
  }

  /* Form controls modernizados dentro del panel de settings */
  #settings-main .form-control {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.04);
    color: #eef2ff;
    border-radius: 8px;
    padding: .5rem .65rem;
  }
  #settings-main .form-control:focus {
    box-shadow: 0 4px 14px rgba(59,130,246,0.12);
    border-color: rgba(59,130,246,0.7);
  }

  /* Botones modernizados dentro del área settings */
  #settings-main .btn-primary {
    background: linear-gradient(180deg,#2563eb,#1d4ed8) !important;
    border: none !important;
    box-shadow: 0 6px 18px rgba(29,78,216,0.18);
    border-radius: 10px !important;
    padding: .45rem .85rem;
  }

  /* Table styling: subtle zebra, bigger spacing and hover */
  #settings-main table.table {
    background: transparent;
    border-collapse: separate;
    border-spacing: 0 6px;
    color: #e6edf3;
  }
  #settings-main table.table thead th {
    border-bottom: none;
    background: transparent;
    color: rgba(255,255,255,0.85);
    font-weight: 600;
    padding-bottom: .5rem;
  }
  #settings-main table.table tbody tr {
    background: rgba(255,255,255,0.02);
    border-radius: 8px;
  }
  #settings-main table.table tbody tr td {
    border-top: 0; padding: .75rem  .75rem;
  }
  #settings-main table.table tbody tr:hover {
    background: rgba(255,255,255,0.035);
    transform: translateY(-1px);
  }

  /* Paginador más visible y acorde al diseño */
  #settings-main .pagination .page-link {
    background: rgba(255,255,255,0.02) !important;
    border: 1px solid rgba(255,255,255,0.04) !important;
    color: #eef2ff !important;
    border-radius: 8px;
  }
  #settings-main .pagination .page-item.active .page-link{
    color: #0f172a !important;
    background: #eef2ff !important;
  }

  /* Pequeño ajuste estético a badges y acciones */
  #settings-main .badge {
    background: rgba(255,255,255,0.06);
    color: #eef2ff;
    border-radius: 8px;
    padding: .25rem .45rem;
  }

  /* Top pagination wrapper: compact and aligned */
  .settings-pagination-top { padding: .15rem .25rem; }
  .settings-pagination-top .pagination { margin: 0; }
  .settings-pagination-top .page-link { padding: .35rem .5rem; font-size: .85rem; }

  /* Force pagination colors to match the settings panel (override theme greens) */
  .settings-pagination-top .page-link {
    background: rgba(255,255,255,0.02) !important;
    border: 1px solid rgba(255,255,255,0.04) !important;
    color: #eef2ff !important;
  }
  .settings-pagination-top .page-item.active .page-link {
    background: #eef2ff !important;
    color: #0f172a !important;
    border-color: rgba(255,255,255,0.12) !important;
  }

  /* Stronger overrides to remove any green coming from theme or component libraries */


    /* Paginador: stronger, high-specificity overrides to neutralize any green theme styles */
    /* Top wrapper sizing */
    .settings-pagination-top { padding: .15rem .25rem; }
    .settings-pagination-top .pagination { margin: 0; }
    .settings-pagination-top .page-link { padding: .35rem .5rem; font-size: .85rem; }

    /* Base neutral style for pagination links inside settings area */
    body .container-fluid #settings-main .settings-pagination-top .pagination .page-link,
    body .container-fluid #settings-main .pagination .page-link,
    #settings-main .settings-pagination-top .pagination .page-link,
    #settings-main .pagination .page-link {
      background-color: rgba(255,255,255,0.02) !important;
      background-image: none !important;
      border: 1px solid rgba(255,255,255,0.04) !important;
      color: #eef2ff !important;
      box-shadow: none !important;
    }

    /* Hover/focus state */
    body .container-fluid #settings-main .settings-pagination-top .pagination .page-link:hover,
    body .container-fluid #settings-main .pagination .page-link:hover,
    #settings-main .settings-pagination-top .pagination .page-link:hover,
    #settings-main .pagination .page-link:hover {
      background-color: rgba(255,255,255,0.06) !important;
      color: #fff !important;
    }

    /* Active page styling */
    body .container-fluid #settings-main .settings-pagination-top .pagination .page-item.active .page-link,
    body .container-fluid #settings-main .pagination .page-item.active .page-link,
    #settings-main .settings-pagination-top .pagination .page-item.active .page-link,
    #settings-main .pagination .page-item.active .page-link {
      background-color: #eef2ff !important;
      background-image: none !important;
      color: #0f172a !important;
      border-color: rgba(255,255,255,0.12) !important;
      box-shadow: none !important;
    }

    /* Override any .btn-success / .bg-success applied by theme or component libs */
    #settings-main .settings-pagination-top .pagination .page-link.bg-success,
    #settings-main .settings-pagination-top .pagination .page-link.btn-success,
    #settings-main .settings-pagination-top .pagination .page-link.text-success,
    #settings-main .pagination .page-link.bg-success,
    #settings-main .pagination .page-link.btn-success,
    #settings-main .pagination .page-link.text-success {
      background-color: rgba(255,255,255,0.02) !important;
      background-image: none !important;
      color: #eef2ff !important;
      border: 1px solid rgba(255,255,255,0.04) !important;
    }

    /* Ensure input-group icons remain visible on the light search input */
    .input-group .input-group-text svg { color: #0f172a; stroke: currentColor; }
    .input-group .input-group-text { background: transparent; border: 0; }

    /* Make search input and category select the same height for symmetry */
    .search-input-wrapper { min-width: 160px; width: 100%; max-width: 420px; }
    .search-input-wrapper .search-with-icon { height: 40px !important; padding-right: 2.2rem !important; padding-left: .75rem !important; }
    .search-input-wrapper .search-icon { top: 50%; transform: translateY(-50%); }
    /* Target the products category select so other selects aren't affected */
    #productsCategorySelect { height: 40px !important; padding-top: .3rem !important; padding-bottom: .3rem !important; }

    /* Note: keeping the filter form horizontal by default; remove mobile wrap to preserve original horizontal layout */

  /* Search icon inside input: ensure visibility and spacing (icon on the right) */
  .search-input-wrapper .search-icon { color: #0f172a; opacity: .95; }
  .search-input-wrapper .search-with-icon { padding-right: 2.2rem !important; padding-left: .5rem !important; }

  /* Hide admin name and profile link when collapsed to avoid overlap */
  #settingsSidebarPanel.sidebar-collapsed .sidebar-user,
  #settingsSidebarPanel.sidebar-collapsed .text-muted.small { display: none !important; }

  /* Hide the textual labels when collapsed so icons are centered */
  #settingsSidebarPanel.sidebar-collapsed .label { display: none !important; }
  #settingsSidebarPanel.sidebar-collapsed .nav-link { padding-left: .5rem; padding-right: .5rem; justify-content: center; }

  /* Center avatar in header when collapsed */
  /* When collapsed, center header content; keep avatar in normal flow so it aligns with nav icons.
     The collapse button is positioned absolute outside the panel (so it won't affect centering). */
  #settingsSidebarPanel.sidebar-collapsed .d-flex.align-items-center.mb-3 { justify-content: center; position: relative; }
  #settingsSidebarPanel.sidebar-collapsed .sidebar-avatar {
    position: static; /* stay in flow */
    left: auto;
    transform: none;
    margin-right: 0;
    margin-left: 0;
    display:inline-flex; align-items:center; justify-content:center;
    z-index: 10;
  }

  /* no body-level shifting when sidebar is inline */
  /* When body has sidebar-collapsed-right, shrink the left column to 64px and let the main expand */
  body.sidebar-collapsed-right .row > .col-md-3,
  body.sidebar-collapsed-right .row > .col-12.col-md-3 {
    flex: 0 0 64px !important;
    max-width: 64px !important;
    transition: flex-basis .22s ease, max-width .22s ease;
  }
  body.sidebar-collapsed-right .row > .col-md-9,
  body.sidebar-collapsed-right .row > .col-12.col-md-9 {
    flex: 1 1 calc(100% - 64px) !important;
    max-width: calc(100% - 64px) !important;
    transition: flex-basis .22s ease, max-width .22s ease;
  }
  /* smooth transitions for main content and sidebar */
  #settingsSidebarPanel { transition: width .22s ease; }
  #settings-main { transition: width .22s ease, margin .18s ease; }

  /* AJAX loading overlay and loading state */
  #settings-main { position: relative; }
  .settings-spinner-overlay { position: absolute; inset: 0; display:flex; align-items:center; justify-content:center; pointer-events:none; }
  .ajax-loading { transition: opacity .18s ease; opacity: 0.45; }

  /* Pagination: force dark theme inside settings main area (overrides green primary) */
  #settings-main .pagination {
    margin: 0;
    --bs-pagination-color: #fff;
    --bs-pagination-bg: transparent;
    --bs-pagination-border-color: rgba(255,255,255,0.06);
    --bs-pagination-hover-color: #fff;
    --bs-pagination-hover-bg: rgba(255,255,255,0.04);
    --bs-pagination-active-color: #000;
    --bs-pagination-active-bg: #fff;
    --bs-pagination-active-border-color: rgba(255,255,255,0.12);
  }
  #settings-main .pagination .page-link{
    color: var(--bs-pagination-color) !important;
    background-color: transparent !important;
    background: var(--bs-pagination-bg) !important;
    border-color: var(--bs-pagination-border-color) !important;
  }
  #settings-main .pagination .page-link:hover{
    color: var(--bs-pagination-hover-color) !important;
    background: var(--bs-pagination-hover-bg) !important;
  }
  #settings-main .pagination .page-item.active .page-link{
    color: var(--bs-pagination-active-color) !important;
    background-color: var(--bs-pagination-active-bg) !important;
    background: var(--bs-pagination-active-bg) !important;
    border-color: var(--bs-pagination-active-border-color) !important;
  }
  /* Strong overrides in case global theme applies .btn-primary styles */
  #settings-main .pagination .page-item.active .page-link,
  #settings-main .pagination .page-link {
    color: inherit !important;
    background-color: transparent !important;
    box-shadow: none !important;
  }
  #settings-main .pagination .page-item.active .page-link{
    color: #000 !important;
    background-color: #ffffff !important;
    border-color: rgba(255,255,255,0.12) !important;
  }

  /* No aggressive spacing overrides here — keep default Bootstrap paddings for visual consistency. */
</style>

{{-- Mobile offcanvas toggler --}}
<div class="d-flex d-md-none mb-2">
  <button class="btn btn-outline-secondary me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#settingsOffcanvas" aria-controls="settingsOffcanvas">☰ Configuración</button>
</div>

{{-- Offcanvas for small screens --}}
<div class="offcanvas offcanvas-start" tabindex="-1" id="settingsOffcanvas" aria-labelledby="settingsOffcanvasLabel">
  <div class="offcanvas-header">
  <h5 class="offcanvas-title" id="settingsOffcanvasLabel">Configuración</h5>
  <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body bg-dark text-white">
    @php
      $adminName = auth()->user()->name ?? 'Admin';
      $parts = array_filter(preg_split('/\s+/', $adminName));
      $initials = '';
      foreach($parts as $p) { $initials .= strtoupper(substr($p,0,1)); if(strlen($initials)>=2) break; }
    @endphp
    <div class="d-flex align-items-center mb-3">
      <div class="sidebar-avatar">{{ $initials }}</div>
      
       
    </div>
  <ul class="nav nav-pills flex-column">
    
    <li class="nav-item mb-1">
      <a href="{{ route('settings.categories.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('settings.categories.*') ? 'active bg-white text-dark' : 'text-white' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 7a2 2 0 0 1 2-2h3l2 2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/></svg>
        <span class="label">Categorías</span>
      </a>
    </li>
    <li class="nav-item mb-1">
      <a href="{{ route('settings.products.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('settings.products.*') ? 'active bg-white text-dark' : 'text-white' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M21 16V8a1 1 0 0 0-.553-.894l-8-4a1 1 0 0 0-.894 0l-8 4A1 1 0 0 0 3 8v8a1 1 0 0 0 .553.894l8 4a1 1 0 0 0 .894 0l8-4A1 1 0 0 0 21 16zM12 3.319 18.447 6 12 8.681 5.553 6 12 3.319zM5 9.236 12 12.681v7.06L5 16.296V9.236zm14 7.06-7 3.445v-7.06L19 9.236v7.06z"/></svg>
        <span class="label">Productos</span>
      </a>
    </li>
    <li class="nav-item mt-2">
      <a class="nav-link disabled text-secondary d-flex align-items-center" href="#" tabindex="-1" aria-disabled="true">
        <span class="me-2">🧾</span>
        <span class="label">Orders</span>
      </a>
    </li>
    </ul>
  </div>
</div>

{{-- Desktop sidebar panel --}}
<aside id="settingsSidebarPanel" class="d-none d-md-block bg-dark text-white p-3 rounded-3">
  @php
    $adminName = auth()->user()->name ?? 'Admin';
    $parts = array_filter(preg_split('/\s+/', $adminName));
    $initials = '';
    foreach($parts as $p) { $initials .= strtoupper(substr($p,0,1)); if(strlen($initials)>=2) break; }
  @endphp
    <div class="d-flex align-items-center mb-3 position-relative">
    <div class="sidebar-avatar">{{ $initials }}</div>
    
      
    <div class="ms-auto">
      <button id="sidebarCollapseBtn" type="button" class="btn btn-light sidebar-toggle" data-bs-toggle="tooltip" data-bs-placement="right" title="Minimizar menú" aria-expanded="true"></button>
    </div>
  </div>

  <ul class="nav nav-pills flex-column">
    
    <li class="nav-item mb-1">
      <a href="{{ route('settings.categories.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('settings.categories.*') ? 'active bg-white text-dark' : 'text-white' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 7a2 2 0 0 1 2-2h3l2 2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/></svg>
        <span class="label">Categorías</span>
      </a>
    </li>

    <li class="nav-item mb-1">
      <a href="{{ route('settings.products.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('settings.products.*') ? 'active bg-white text-dark' : 'text-white' }}">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M21 16V8a1 1 0 0 0-.553-.894l-8-4a1 1 0 0 0-.894 0l-8 4A1 1 0 0 0 3 8v8a1 1 0 0 0 .553.894l8 4a1 1 0 0 0 .894 0l8-4A1 1 0 0 0 21 16zM12 3.319 18.447 6 12 8.681 5.553 6 12 3.319zM5 9.236 12 12.681v7.06L5 16.296V9.236zm14 7.06-7 3.445v-7.06L19 9.236v7.06z"/></svg>
        <span class="label">Productos</span>
      </a>
    </li>

    <li class="nav-item mt-2">
      <a class="nav-link disabled text-secondary d-flex align-items-center" href="#" tabindex="-1" aria-disabled="true">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 3h18v2H3zM3 7h18v2H3zM3 11h18v2H3zM3 15h18v2H3z"/></svg>
        <span class="label">Otros</span>
      </a>
    </li>
  </ul>
</aside>

<script>
  (function(){
    function initSidebarToggle(){
      const panel = document.getElementById('settingsSidebarPanel');
      const btn = document.getElementById('sidebarCollapseBtn');
      if(!panel || !btn) return;

      // small SVG icons for the toggle
      const iconLeft = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>`;
      const iconRight = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>`;

      // tooltips initializer (safe)
      function initTooltips(){
        try{
          const tooltipEls = Array.prototype.slice.call(panel.querySelectorAll('[data-bs-toggle="tooltip"]'));
          tooltipEls.forEach(el=>{
            try{ if(!bootstrap.Tooltip.getInstance(el)) new bootstrap.Tooltip(el); }catch(e){ try{ new bootstrap.Tooltip(el); }catch(_){} }
          });
        }catch(e){ /* bootstrap not available */ }
      }

      // set body class so main content can shift if desired
      function applyBodyClass(){
        if(document.body){
          if(panel.classList.contains('sidebar-collapsed')){
            document.body.classList.add('sidebar-collapsed-right');
            document.body.classList.remove('sidebar-expanded-right');
          } else {
            document.body.classList.add('sidebar-expanded-right');
            document.body.classList.remove('sidebar-collapsed-right');
          }
        }
      }

      // update button icon + tooltip text
      function setBtnIcon(isCollapsed){
        btn.innerHTML = isCollapsed ? iconRight : iconLeft;
        btn.setAttribute('title', isCollapsed ? 'Abrir menú' : 'Minimizar menú');
        btn.setAttribute('aria-expanded', (!isCollapsed).toString());
        // update tooltip placement so it doesn't overflow when button is outside
        const placement = isCollapsed ? 'left' : 'right';
        btn.setAttribute('data-bs-placement', placement);
        try{
          const inst = bootstrap.Tooltip.getInstance(btn);
          if(inst) inst.dispose();
        }catch(e){}
        try{ new bootstrap.Tooltip(btn, {placement}); }catch(e){}
      }

  // start expanded by default (ignore stored state for initial load)
  // If you want to respect previous user preference, read localStorage here.
  setBtnIcon(panel.classList.contains('sidebar-collapsed'));
  initTooltips();
  applyBodyClass();

      btn.addEventListener('click', ()=>{
        panel.classList.toggle('sidebar-collapsed');
        const isCollapsed = panel.classList.contains('sidebar-collapsed');
        localStorage.setItem('settingsSidebarCollapsed', isCollapsed ? '1' : '0');
        setBtnIcon(isCollapsed);
        applyBodyClass();
      });

      // No hover expansion: sidebar expands/collapses only when clicking the button
    }

    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', initSidebarToggle);
    } else {
      initSidebarToggle();
    }
  })();
</script>
<script>
  // Forzar estilos de paginación mediante inline styles con !important
  // Esto garantiza que el tema/`public/css/estilo.css` no pinte el paginador en verde
  (function(){
    function forcePaginationStyles(){
      try{
        const links = document.querySelectorAll('#settings-main .settings-pagination-top .page-link, #settings-main .pagination .page-link');
        links.forEach(el=>{
          el.style.setProperty('background-color', 'rgba(255,255,255,0.02)', 'important');
          el.style.setProperty('background-image', 'none', 'important');
          el.style.setProperty('color', '#eef2ff', 'important');
          el.style.setProperty('border-color', 'rgba(255,255,255,0.04)', 'important');
          el.style.setProperty('box-shadow', 'none', 'important');
        });

        const active = document.querySelectorAll('#settings-main .settings-pagination-top .page-item.active .page-link, #settings-main .pagination .page-item.active .page-link');
        active.forEach(el=>{
          el.style.setProperty('background-color', '#eef2ff', 'important');
          el.style.setProperty('color', '#0f172a', 'important');
          el.style.setProperty('border-color', 'rgba(255,255,255,0.12)', 'important');
          el.style.setProperty('box-shadow', 'none', 'important');
        });
      }catch(e){ console.error('forcePaginationStyles error', e); }
    }

    if(document.readyState === 'loading'){
      document.addEventListener('DOMContentLoaded', forcePaginationStyles);
    } else {
      forcePaginationStyles();
    }
    // Re-apply after PJAX-like updates or if content changes dynamically (safe no-op)
    window.addEventListener('load', forcePaginationStyles);

    // Observe changes inside #settings-main and reapply styles when pagination is re-rendered
    (function(){
      const container = document.getElementById('settings-main');
      if(!container) return;
      let scheduled = false;
      const mo = new MutationObserver((mutations)=>{
        if(scheduled) return;
        scheduled = true;
        // debounce a little to avoid thrashing
        setTimeout(()=>{
          forcePaginationStyles();
          scheduled = false;
        }, 120);
      });
      mo.observe(container, { childList: true, subtree: true, attributes: true });

      // also re-run once after 600ms as a safety net in case styles are applied slightly later
      setTimeout(forcePaginationStyles, 600);
    })();
  })();
</script>
<!-- Removed AJAX loader script: navigation now uses standard full-page requests. -->