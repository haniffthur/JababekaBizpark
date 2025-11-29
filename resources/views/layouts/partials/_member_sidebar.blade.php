{{-- resources/views/layouts/partials/_member_sidebar.blade.php --}}

<hr class="sidebar-divider">
<div class="sidebar-heading">
    Member
</div>

@php 
    // Grup ini aktif jika salah satu dari 3 rute aset diakses
    $memberAsetActive = request()->routeIs('member.trucks.*') || 
                        request()->routeIs('member.qrcodes.*') || 
                        request()->routeIs('member.personal_qrs.*'); 
@endphp
<li class="nav-item {{ $memberAsetActive ? 'active' : '' }}">
    <a class="nav-link {{ $memberAsetActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseAsetMember">
        <i class="fas fa-truck text-primary"></i>
        <span class="ml-2">Manajemen Aset</span>
    </a>
    <div id="collapseAsetMember" class="collapse {{ $memberAsetActive ? 'show' : '' }}" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Aset Saya:</h6>
            <a class="collapse-item {{ request()->routeIs('member.trucks.*') ? 'active' : '' }}" 
               href="{{ route('member.trucks.index') }}">Manajemen Truk</a>
            <a class="collapse-item {{ request()->routeIs('member.qrcodes.*') ? 'active' : '' }}" 
               href="{{ route('member.qrcodes.index') }}">Manajemen QR Truk</a>
            <a class="collapse-item {{ request()->routeIs('member.personal_qrs.*') ? 'active' : '' }}" 
               href="{{ route('member.personal_qrs.index') }}">QR Pribadi Saya</a>
        </div>
    </div>
</li>

@php 
    // Grup ini aktif jika salah satu dari 2 rute laporan diakses
    $memberLaporanActive = request()->routeIs('member.billings.*') || request()->routeIs('member.gate.logs'); 
@endphp
<li class="nav-item {{ $memberLaporanActive ? 'active' : '' }}">
    <a class="nav-link {{ $memberLaporanActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseLaporanMember">
        <i class="fas fa-file-alt text-primary"></i>
        <span class="ml-2">Laporan & Tagihan</span>
    </a>
    <div id="collapseLaporanMember" class="collapse {{ $memberLaporanActive ? 'show' : '' }}" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Laporan Saya:</h6>
            <!-- <a class="collapse-item {{ request()->routeIs('member.billings.*') ? 'active' : '' }}" 
               href="{{ route('member.billings.index') }}">Tagihan Saya</a> -->
               <a class="collapse-item {{ request()->routeIs('member.ipl.index') ? 'active' : '' }}" 
   href="{{ route('member.ipl.index') }}">Tagihan IPL Bulanan</a>
            <a class="collapse-item {{ request()->routeIs('member.gate.logs') ? 'active' : '' }}" 
               href="{{ route('member.gate.logs') }}">Histori Truk</a>
        </div>
    </div>
</li>