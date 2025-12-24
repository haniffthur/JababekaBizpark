@extends('layouts.app')

{{-- Kita tambahkan @push('styles') untuk CSS khusus print --}}
@push('styles')
<style>
    /* Sembunyikan sidebar, topbar, dan footer saat print */
    @media print {
        #accordionSidebar, #page-top #content-wrapper footer, #content nav.navbar {
            display: none !important;
        }
        
        /* Pastikan konten utama mengambil lebar penuh */
        #content-wrapper {
            margin-left: 0 !important;
        }

        /* Hilangkan box shadow dan border pada card */
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        /* Sembunyikan tombol */
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4 no-print">
    <h1 class="h3 mb-0 text-gray-800">Lihat/Cetak QR Code</h1>
    <div>
        <a href="{{ route('member.qrcodes.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
        <button onclick="window.print();" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-print fa-sm text-white-50"></i> Cetak Halaman Ini
        </button>
        
        <a href="{{ route('member.qrcodes.download', $qrcode->id) }}" class="btn btn-sm btn-success shadow-sm">
            <i class="fas fa-download fa-sm text-white-50"></i> Download PDF
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detail QR Code</h6>
    </div>
    <div class="card-body text-center">
        
        <h4 class="mb-3">Truk: <strong>{{ $qrcode->truck->license_plate }}</strong></h4>
        
        <div class="mb-3">
            Status:
            @if ($qrcode->status == 'baru')
                <span class="badge badge-primary">Baru</span>
            @elseif ($qrcode->status == 'aktif')
                <span class="badge badge-success">Aktif (Di Dalam)</span>
            @else
                <span class="badge badge-secondary">Selesai</span>
            @endif
        </div>

        <hr class="my-4">

        <!-- <h5 class="mb-3">Kode: <code>{{ $qrcode->code }}</code></h5> -->

       <div class="d-flex justify-content-center">
    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($qrcode->code) !!}
</div>

        <p class="mt-4 text-muted">
            Cetak halaman ini dan berikan QR Code kepada supir.
        </p>

    </div>
</div>

@endsection