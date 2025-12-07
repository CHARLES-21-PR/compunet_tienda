<!-- Modern sidebar: Offcanvas on mobile + collapsible panel on desktop -->
<style>
  /* Sidebar panel for desktop: fixed left */
  #settingsSidebarPanel {
    z-index: 1040; /* above content */
    transition: width .22s ease;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    overflow: visible;
    display: flex;
    flex-direction: column;
    border-right: 1px solid rgba(255,255,255,0.05);
  }

  /* Content wrapper adjustment */
  @media (min-width: 768px) {
    #settings-main {
      margin-left: 260px;
      transition: margin-left .22s ease;
      width: auto !important; /* Override col-12 width */
      flex: none !important;
      max-width: none !important;
    }
    /* When sidebar is collapsed */
    body.sidebar-collapsed-desktop #settingsSidebarPanel {
      width: 80px;
    }
    body.sidebar-collapsed-desktop #settings-main {
      margin-left: 80px;
    }
  }

  /* Custom scrollbar for the sidebar content */
  .sidebar-content-wrapper {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    /* Hide scrollbar for Firefox */
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.2) transparent;
  }
  /* Hide scrollbar for Chrome/Safari/Edge */
  .sidebar-content-wrapper::-webkit-scrollbar {
    width: 4px;
  }
  .sidebar-content-wrapper::-webkit-scrollbar-track {
    background: transparent;
  }
  .sidebar-content-wrapper::-webkit-scrollbar-thumb {
    background-color: rgba(255,255,255,0.2);
    border-radius: 4px;
  }

  /* Toggle button: positioned at top-right inside sidebar */
  .sidebar-toggle {
    position: absolute;
    right: -15px;
    top: 20px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    background: #3b82f6;
    color: #fff;
    border: 2px solid #1f2937;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 1050;
  }
  .sidebar-toggle:hover {
    background: #2563eb;
    transform: scale(1.1);
  }

  /* avatar / admin icon */
  .sidebar-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.1rem;
    box-shadow: 0 4px 10px rgba(37,99,235,0.3);
    transition: all 0.2s ease;
  }

  /* Collapsed state styles */
  body.sidebar-collapsed-desktop #settingsSidebarPanel .sidebar-avatar {
    width: 40px;
    height: 40px;
    font-size: 1rem;
    margin: 0 auto;
  }
  
  body.sidebar-collapsed-desktop #settingsSidebarPanel .user-card-container {
    justify-content: center;
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    margin-bottom: 1rem !important;
  }
  
  body.sidebar-collapsed-desktop #settingsSidebarPanel .sidebar-user-info {
    display: none;
  }

  /* Center icons and make links compact when collapsed */
  body.sidebar-collapsed-desktop #settingsSidebarPanel .nav-link {
    justify-content: center !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    height: 48px;
  }
  
  body.sidebar-collapsed-desktop #settingsSidebarPanel .nav-link .me-2 {
    margin-right: 0 !important;
    margin-left: 0 !important;
  }
  
  body.sidebar-collapsed-desktop #settingsSidebarPanel .label { display: none !important; }

  /* Modern Header Bar for Mobile */
  .mobile-admin-header {
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  }

  /* Allow tables and wide content inside #settings-main to scroll instead of
     forcing the layout to overflow the viewport at awkward breakpoints. */
  #settings-main { min-width: 0; padding-left: 0 !important; padding-right: 0 !important; }
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
    margin: 1rem 0 !important;
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
  #settingsSidebarPanel.sidebar-collapsed .sidebar-role,
  #settingsSidebarPanel.sidebar-collapsed .sidebar-user-info,
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
  /* When body has sidebar-collapsed-right, shrink the left column to 70px and let the main expand */
  body.sidebar-collapsed-right .row > .col-md-3,
  body.sidebar-collapsed-right .row > .col-12.col-md-3 {
    flex: 0 0 70px !important;
    max-width: 70px !important;
    transition: flex-basis .22s ease, max-width .22s ease;
  }
  body.sidebar-collapsed-right .row > .col-md-9,
  body.sidebar-collapsed-right .row > .col-12.col-md-9 {
    flex: 1 1 calc(100% - 70px) !important;
    max-width: calc(100% - 70px) !important;
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

{{-- Desktop Sidebar Panel (Fixed) --}}
<div id="settingsSidebarPanel" class="bg-dark text-white d-none d-md-flex flex-column p-3">
    {{-- Toggle Button --}}
    <div id="sidebarCollapseBtn" class="sidebar-toggle" data-bs-toggle="tooltip" data-bs-placement="right" title="Minimizar menú">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg>
    </div>

    {{-- User Info --}}
    @php
      $adminName = auth()->user()->name ?? 'Admin';
      $parts = array_filter(preg_split('/\s+/', $adminName));
      $initials = '';
      foreach($parts as $p) { $initials .= strtoupper(substr($p,0,1)); if(strlen($initials)>=2) break; }
    @endphp
    <div class="user-card-container d-flex align-items-center mb-4 p-2 rounded" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05);">
      <div class="sidebar-avatar shadow-sm">{{ $initials }}</div>
      <div class="ms-3 flex-grow-1 overflow-hidden sidebar-user-info" style="line-height: 1.2;">
        <div class="fw-bold text-white text-truncate" style="font-size: 0.9rem;">{{ $adminName }}</div>
        <div class="text-white-50 small text-truncate" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">{{ auth()->user()->role }}</div>
      </div>
    </div>

    {{-- Navigation --}}
    <div class="sidebar-content-wrapper">
        <ul class="nav nav-pills flex-column gap-1">
            <li class="nav-item">
              <a href="{{ route('admin.dashboard.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard.*') ? 'active bg-primary text-white shadow-sm' : 'text-white-50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 3h2v18H3V3zm6 6h2v12H9V9zm6-6h2v18h-2V3z"/></svg>
                <span class="label">Dashboard</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.categories.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.categories.*') ? 'active bg-primary text-white shadow-sm' : 'text-white-50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 7a2 2 0 0 1 2-2h3l2 2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/></svg>
                <span class="label">Categorías</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.products.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.products.*') ? 'active bg-primary text-white shadow-sm' : 'text-white-50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M21 16V8a1 1 0 0 0-.553-.894l-8-4a1 1 0 0 0-.894 0l-8 4A1 1 0 0 0 3 8v8a1 1 0 0 0 .553.894l8 4a1 1 0 0 0 .894 0l8-4A1 1 0 0 0 21 16zM12 3.319 18.447 6 12 8.681 5.553 6 12 3.319zM5 9.236 12 12.681v7.06L5 16.296V9.236zm14 7.06-7 3.445v-7.06L19 9.236v7.06z"/></svg>
                <span class="label">Productos</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.orders.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.*') ? 'active bg-primary text-white shadow-sm' : 'text-white-50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M21 6h-8l-2-2H3v16h18V6zm-2 10H5V8h4.17L11 9.83V16h8v0z"/></svg>
                <span class="label">Pedidos</span>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{ route('admin.clients.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.clients.*') ? 'active bg-primary text-white shadow-sm' : 'text-white-50' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
                <span class="label">Clientes</span>
              </a>
            </li>

            <li class="nav-item mt-auto pt-3 border-top border-secondary">
              <a href="{{ url('/') }}" class="nav-link d-flex align-items-center text-white-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                <span class="label">Ir a la Tienda</span>
              </a>
            </li>
        </ul>
    </div>
</div>

{{-- Mobile offcanvas toggler --}}
<div class="mobile-admin-header d-md-none">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-white p-0 me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#settingsOffcanvas" aria-controls="settingsOffcanvas">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
            </svg>
        </button>
        <span class="h5 mb-0 text-white fw-bold">Panel Admin</span>
    </div>
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
          <div class="ms-3 flex-grow-1 overflow-hidden sidebar-user-info" style="line-height: 1.2;">
            <div class="fw-bold text-white text-truncate" style="font-size: 0.95rem; letter-spacing: 0.3px;">{{ $adminName }}</div>
            <div class="text-white-50 small text-truncate" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">{{ auth()->user()->role }}</div>
          </div>
        </div>
      <ul class="nav nav-pills flex-column">
        <li class="nav-item mb-1">
          <a href="{{ route('admin.dashboard.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.dashboard.*') ? 'active bg-white text-dark' : 'text-white' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 3h2v18H3V3zm6 6h2v12H9V9zm6-6h2v18h-2V3z"/></svg>
            <span class="label">Dashboard</span>
          </a>
        </li>
        
        
        <li class="nav-item mb-1">
          <a href="{{ route('admin.categories.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.categories.*') ? 'active bg-white text-dark' : 'text-white' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M3 7a2 2 0 0 1 2-2h3l2 2h7a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z"/></svg>
            <span class="label">Categorías</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="{{ route('admin.products.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.products.*') ? 'active bg-white text-dark' : 'text-white' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M21 16V8a1 1 0 0 0-.553-.894l-8-4a1 1 0 0 0-.894 0l-8 4A1 1 0 0 0 3 8v8a1 1 0 0 0 .553.894l8 4a1 1 0 0 0 .894 0l8-4A1 1 0 0 0 21 16zM12 3.319 18.447 6 12 8.681 5.553 6 12 3.319zM5 9.236 12 12.681v7.06L5 16.296V9.236zm14 7.06-7 3.445v-7.06L19 9.236v7.06z"/></svg>
            <span class="label">Productos</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="{{ route('admin.orders.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.orders.*') ? 'active bg-white text-dark' : 'text-white' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M21 6h-8l-2-2H3v16h18V6zm-2 10H5V8h4.17L11 9.83V16h8v0z"/></svg>
            <span class="label">Pedidos</span>
          </a>
        </li>
        <li class="nav-item mb-1">
          <a href="{{ route('admin.clients.index') }}" class="nav-link d-flex align-items-center {{ request()->routeIs('admin.clients.*') ? 'active bg-white text-dark' : 'text-white' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/></svg>
            <span class="label">Clientes</span>
          </a>
        </li>
        
        <li class="nav-item mb-1 mt-3 pt-3 border-top" style="border-color: rgba(255,255,255,0.1) !important;">
          <a href="{{ url('/') }}" class="nav-link d-flex align-items-center text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="me-2"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span class="label">Ir a la Tienda</span>
          </a>
        </li>
        
        </ul>
      </div>
    </div>
</div>

<script>
  (function(){
    function initSidebarToggle(){
      const panel = document.getElementById('settingsSidebarPanel');
      const btn = document.getElementById('sidebarCollapseBtn');
      if(!panel || !btn) return;

      // small SVG icons for the toggle
      const iconLeft = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg>`;
      const iconRight = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>`;

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
            document.body.classList.add('sidebar-collapsed-desktop');
          } else {
            document.body.classList.remove('sidebar-collapsed-desktop');
          }
        }
      }

      // update button icon + tooltip text
      function setBtnIcon(isCollapsed){
        btn.innerHTML = isCollapsed ? iconRight : iconLeft;
        btn.setAttribute('title', isCollapsed ? 'Expandir menú' : 'Minimizar menú');
        btn.setAttribute('aria-expanded', (!isCollapsed).toString());
        // update tooltip placement so it doesn't overflow when button is outside
        const placement = isCollapsed ? 'right' : 'left';
        btn.setAttribute('data-bs-placement', placement);
        try{
          const inst = bootstrap.Tooltip.getInstance(btn);
          if(inst) inst.dispose();
        }catch(e){}
        try{ new bootstrap.Tooltip(btn, {placement}); }catch(e){}
      }

  // start expanded by default (ignore stored state for initial load)
  // If you want to respect previous user preference, read localStorage here.
  // setBtnIcon(panel.classList.contains('sidebar-collapsed'));
  // initTooltips();
  // applyBodyClass();

      btn.addEventListener('click', ()=>{
        panel.classList.toggle('sidebar-collapsed');
        const isCollapsed = panel.classList.contains('sidebar-collapsed');
        localStorage.setItem('settingsSidebarCollapsed', isCollapsed ? '1' : '0');
        setBtnIcon(isCollapsed);
        applyBodyClass();
      });
      
      // Initialize state
      const storedState = localStorage.getItem('settingsSidebarCollapsed');
      if(storedState === '1'){
        panel.classList.add('sidebar-collapsed');
      } else {
        panel.classList.remove('sidebar-collapsed');
      }
      setBtnIcon(panel.classList.contains('sidebar-collapsed'));
      applyBodyClass();
      initTooltips();

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