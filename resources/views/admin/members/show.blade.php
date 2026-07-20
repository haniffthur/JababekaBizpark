@extends('layouts.app')

@section('content')

{{-- ==========================================
   1. HEADER HALAMAN & TOMBOL AKSI UTAMA
========================================== --}}
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Member: {{ $member->name }}</h1>
    <div>
        <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
        <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-sm btn-info shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Member
        </a>
    </div>
</div>

{{-- ALERT PESAN SUKSES --}}
@if (session('success'))
    <div class="alert alert-success shadow-sm mb-4">
        {{ session('success') }}
    </div>
@endif

{{-- ==========================================
   2. KARTU INFORMASI AKUN
========================================== --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <strong>Nama Lengkap:</strong>
                        <p class="mb-0">{{ $member->name }}</p>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <strong>Email:</strong>
                        <p class="mb-0">{{ $member->email }}</p>
                    </div>
                    <div class="col-md-2 mb-3 mb-md-0">
                        <strong>Status IPL:</strong>
                        <p class="mb-0">
                            @if($member->ipl_status == 'paid')
                                <span class="badge badge-success">Lunas (Paid)</span>
                            @else
                                <span class="badge badge-danger">Belum Bayar</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-2">
                        <strong>Bergabung Sejak:</strong>
                        <p class="mb-0">{{ $member->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
   3. KARTU QR CODE PRIBADI
========================================== --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success">QR Code Pribadi</h6>
                <div>
                    <a href="{{ route('admin.members.personal-qrs.pdf', $member->id) }}" class="btn btn-sm btn-outline-success shadow-sm mr-1" target="_blank">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </a>
                    <a href="{{ route('admin.members.personal-qrs.create', $member->id) }}" class="btn btn-sm btn-success shadow-sm">
                        <i class="fas fa-plus fa-sm"></i> Tambah QR
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama Slot</th>
                                <th>Plat Nomor</th>
                                <th class="text-center">Gambar QR</th>
                                <th>Kode Unik</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($member->personalQrs as $qr)
                                <tr>
                                    <td class="align-middle">{{ $qr->name }}</td>
                                    <td class="align-middle"><strong class="text-uppercase">{{ $qr->license_plate }}</strong></td>
                                    <td class="text-center align-middle">
                                        {{-- Render Gambar QR Code --}}
                                        <div class="bg-white p-2 d-inline-block border rounded shadow-sm text-center">
    {{-- Lebar batang: 2, Tinggi batang: 40 --}}
    <img src="data:image/png;base64,{!! DNS1D::getBarcodePNG($qr->code, 'C128', 2, 40) !!}" alt="barcode" />
</div>
                                    </td>
                                    <td class="align-middle"><code class="text-danger font-weight-bold">{{ $qr->code }}</code></td>
                                    <td class="align-middle">
                                        @if ($qr->status == 'aktif')
                                            <span class="badge badge-success">Di Dalam</span>
                                        @else
                                            <span class="badge badge-secondary">Di Luar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada QR Code Pribadi yang didaftarkan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- ==========================================
       4. KARTU ARMADA TRUK (KOLOM KIRI)
    ========================================== --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4 h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Armada Truk ({{ $member->trucks->count() }})</h6>
                <div>
                    <a href="{{ route('admin.members.trucks.pdf', $member->id) }}" class="btn btn-sm btn-outline-primary shadow-sm mr-1" target="_blank">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </a>
                    <a href="{{ route('admin.members.trucks.create', $member->id) }}" class="btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-truck"></i> Tambah Truk
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>Plat Nomor</th>
                                <th class="text-center">Gambar QR</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($member->trucks as $truck)
                                <tr>
                                    <td class="align-middle">
                                        <strong class="text-uppercase">{{ $truck->license_plate }}</strong><br>
                                        <small class="text-muted">{{ $truck->driver_name ?? 'Tanpa Supir' }}</small>
                                    </td>
                                    <td class="text-center align-middle py-2">
                                        {{-- Render Gambar QR Code Truk --}}
                                       {{-- Render Gambar Barcode Truk --}}
@if($truck->qrCode)
    <div class="bg-white p-2 d-inline-block border rounded mb-1 shadow-sm text-center">
        {{-- Menggunakan DNS1D untuk Barcode Batang --}}
        <img src="data:image/png;base64,{!! DNS1D::getBarcodePNG($truck->qrCode->code, 'C128', 1.5, 30) !!}" alt="Barcode Truk" />
    </div>
    <br>
    <!-- <code class="text-primary small font-weight-bold">{{ $truck->qrCode->code }}</code> -->
@else
                                            <span class="text-muted small font-italic">Belum ada QR</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if ($truck->is_inside)
                                            <span class="badge badge-success">Di Dalam</span>
                                        @else
                                            <span class="badge badge-secondary">Di Luar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4 small">Tidak ada armada truk yang terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ==========================================
       5. KARTU RIWAYAT TAGIHAN (KOLOM KANAN)
    ========================================== --}}
    <div class="col-lg-6">
        <div class="card shadow mb-4 h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Tagihan Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" width="100%" cellspacing="0">
                        <thead class="bg-light">
                            <tr>
                                <th>ID</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Ambil maksimal 5 tagihan terakhir --}}
                            @forelse ($member->billings->sortByDesc('created_at')->take(5) as $bill)
                                <tr>
                                    <td class="align-middle">#{{ $bill->id }}</td>
                                    <td class="align-middle">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</td>
                                    <td class="align-middle">
                                        @if ($bill->status == 'paid')
                                            <span class="badge badge-success">Lunas</span>
                                        @elseif ($bill->status == 'pending_verification')
                                            <span class="badge badge-info">Verifikasi</span>
                                        @elseif ($bill->status == 'rejected')
                                            <span class="badge badge-danger">Ditolak</span>
                                        @else
                                            <span class="badge badge-warning">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">{{ $bill->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4 small">Belum ada riwayat tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection