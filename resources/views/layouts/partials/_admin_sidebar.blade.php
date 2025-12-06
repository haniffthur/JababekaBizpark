{{-- resources/views/layouts/partials/_admin_sidebar.blade.php --}}

<hr class="sidebar-divider">

{{-- BAGIAN 1: DATA MASTER --}}
<div class="sidebar-heading">
    Data Master
</div>

<li class="nav-item {{ request()->routeIs('admin.members.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.members.index') }}">
        <i class="fas fa-fw fa-users text-primary"></i>
        <span class="ml-2">Manajemen Member</span>
    </a>
</li>

<hr class="sidebar-divider">

{{-- BAGIAN 2: OPERASIONAL --}}
<div class="sidebar-heading">
    Operasional
</div>

<li class="nav-item {{ request()->routeIs('admin.billings.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.billings.index') }}">
        <i class="fas fa-fw fa-file-invoice-dollar text-primary"></i>
        <span class="ml-2">Manajemen Keuangan</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.qr.approvals.index') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.qr.approvals.index') }}">
        <i class="fas fa-fw fa-check-circle text-primary"></i>
        <span class="ml-2">Persetujuan QR</span>
        
        {{-- Badge Notifikasi (Tetap Ada & Konsisten) --}}
        <span id="sidebar-pending-badge" class="badge badge-danger badge-counter ml-1" 
              style="font-size: 0.7rem; display: {{ (isset($pendingQrCount) && $pendingQrCount > 0) ? 'inline-block' : 'none' }};">
            {{ $pendingQrCount ?? 0 }}
        </span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.gate.logs') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.gate.logs') }}">
        <i class="fas fa-fw fa-file-alt text-primary"></i>
        <span class="ml-2">Laporan / Log Sistem</span>
    </a>
</li>

<hr class="sidebar-divider">

{{-- BAGIAN 3: PENGATURAN --}}
<div class="sidebar-heading">
    Pengaturan
</div>

<li class="nav-item {{ request()->routeIs('admin.gate-machines.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.gate-machines.index') }}">
        <i class="fas fa-fw fa-server text-primary"></i>
        <span class="ml-2">Manajemen Mesin Gate</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.personal-qrs.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.personal-qrs.index') }}">
        <i class="fas fa-fw fa-id-badge text-primary"></i>
        <span class="ml-2">Manajemen QR Pribadi</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.settings.index') }}">
        <i class="fas fa-fw fa-cogs text-primary"></i>
        <span class="ml-2">Pengaturan Sistem</span>
    </a>
</li>