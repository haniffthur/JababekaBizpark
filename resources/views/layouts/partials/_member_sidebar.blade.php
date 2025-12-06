{{-- resources/views/layouts/partials/_member_sidebar.blade.php --}}

<hr class="sidebar-divider">

{{-- BAGIAN 1: ASET SAYA --}}
<div class="sidebar-heading">
    Aset Saya
</div>

<li class="nav-item {{ request()->routeIs('member.trucks.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('member.trucks.index') }}">
        <i class="fas fa-fw fa-truck text-primary"></i>
        <span class="ml-2">Manajemen Truk</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('member.qrcodes.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('member.qrcodes.index') }}">
        <i class="fas fa-fw fa-qrcode text-primary"></i>
        <span class="ml-2">Cetak QR Truk</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('member.personal_qrs.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('member.personal_qrs.index') }}">
        <i class="fas fa-fw fa-id-badge text-primary"></i>
        <span class="ml-2">QR Pribadi Saya</span>
    </a>
</li>

<hr class="sidebar-divider">

{{-- BAGIAN 2: LAPORAN & TAGIHAN --}}
<div class="sidebar-heading">
    Laporan & Tagihan
</div>

<li class="nav-item {{ request()->routeIs('member.billings.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('member.billings.index') }}">
        <i class="fas fa-fw fa-file-invoice-dollar text-primary"></i>
        <span class="ml-2">Tagihan Saya</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('member.gate.logs') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('member.gate.logs') }}">
        <i class="fas fa-fw fa-history text-primary"></i>
        <span class="ml-2">Histori Truk</span>
    </a>
</li>