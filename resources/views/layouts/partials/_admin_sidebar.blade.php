{{-- resources/views/layouts/partials/_admin_sidebar.blade.php --}}

<hr class="sidebar-divider">
<div class="sidebar-heading">
    Admin
</div>

@php 
    $adminDataActive = request()->routeIs('admin.members.*'); 
@endphp
<li class="nav-item {{ $adminDataActive ? 'active' : '' }}">
    <a class="nav-link {{ $adminDataActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseDataMaster">
        <i class="fas fa-database text-primary"></i>
        <span class="ml-2">Manajemen Data</span>
    </a>
    <div id="collapseDataMaster" class="collapse {{ $adminDataActive ? 'show' : '' }}" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Data Master:</h6>
            <a class="collapse-item {{ request()->routeIs('admin.members.*') ? 'active' : '' }}" 
               href="{{ route('admin.members.index') }}">Manajemen Member</a>
        </div>
    </div>
</li>

@php 
    // Hapus 'admin.billings.*' dari pengecekan aktif
    $adminOpsActive = request()->routeIs('admin.gate.logs') || request()->routeIs('admin.qr.approvals.index'); 
@endphp
<li class="nav-item {{ $adminOpsActive ? 'active' : '' }}">
    <a class="nav-link {{ $adminOpsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseOperasional">
        <i class="fas fa-chart-area text-primary"></i>
        <span class="ml-2">Operasional</span>
    </a>
    <div id="collapseOperasional" class="collapse {{ $adminOpsActive ? 'show' : '' }}" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Laporan & Persetujuan:</h6>
            
            {{-- MENU KEUANGAN DIHAPUS/DIKOMENTARI --}}
            {{-- 
            <a class="collapse-item {{ request()->routeIs('admin.billings.*') ? 'active' : '' }}" 
               href="{{ route('admin.billings.index') }}">Manajemen Keuangan</a>
            --}}
            
            <a class="collapse-item {{ request()->routeIs('admin.qr.approvals.index') ? 'active' : '' }}" 
               href="{{ route('admin.qr.approvals.index') }}">Persetujuan QR Code</a>
            
            <a class="collapse-item {{ request()->routeIs('admin.gate.logs') ? 'active' : '' }}" 
               href="{{ route('admin.gate.logs') }}">Laporan / Log Sistem</a>
        </div>
    </div>
</li>

@php 
    $adminSettingsActive = request()->routeIs('admin.settings.*') || 
                           request()->routeIs('admin.personal-qrs.*') || 
                           request()->routeIs('admin.gate-machines.*'); 
@endphp
<li class="nav-item {{ $adminSettingsActive ? 'active' : '' }}">
    <a class="nav-link {{ $adminSettingsActive ? '' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#collapseSistem">
        <i class="fas fa-fw fa-cogs text-primary"></i>
        <span class="ml-2">Pengaturan</span>
    </a>
    <div id="collapseSistem" class="collapse {{ $adminSettingsActive ? 'show' : '' }}" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Sistem & Aset:</h6>
            <a class="collapse-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" 
               href="{{ route('admin.settings.index') }}">Pengaturan Sistem</a>
            
            <a class="collapse-item {{ request()->routeIs('admin.gate-machines.*') ? 'active' : '' }}" 
               href="{{ route('admin.gate-machines.index') }}">Manajemen Mesin Gate</a>

           
        </div>
    </div>
</li>