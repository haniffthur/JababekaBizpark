{{-- resources/views/layouts/sidebar.blade.php --}}

{{-- CSS KHUSUS UNTUK EFEK BLUR/SHADOW DI KANAN --}}
<style>
    .sidebar {
        /* Efek bayangan halus di sebelah kanan */
        box-shadow: 5px 0 20px rgba(0, 0, 0, 0.08) !important; 
        
        /* Pastikan sidebar berada di lapisan atas agar bayangan terlihat menimpa konten */
        z-index: 10; 
        position: relative;
    }
</style>

<ul class="navbar-nav bg-white sidebar sidebar-light accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center py-4" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon text-primary">
            {{-- Ganti dengan logo GudangJababeka jika ada --}}
            <i class="fas fa-warehouse fa-lg"></i>
        </div>
        <div class="sidebar-brand-text mx-2">BizparkJ</div>
    </a>

    <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-home text-primary"></i>
            <span class="ml-2">Dashboard</span>
        </a>
    </li>

    @if (auth()->user()->role == 'admin')
        
        {{-- Muat menu khusus Admin --}}
        @include('layouts.partials._admin_sidebar')

    @elseif (auth()->user()->role == 'member')
        
        {{-- Muat menu khusus Member --}}
        @include('layouts.partials._member_sidebar')

    @endif
    
    <hr class="sidebar-divider d-none d-md-block">

    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0 bg-light" id="sidebarToggle"></button>
    </div>

</ul>