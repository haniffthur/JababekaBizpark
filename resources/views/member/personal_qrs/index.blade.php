@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">QR Code Pribadi Saya</h1>
</div>

<div class="alert alert-info shadow-sm">
    <h6 class="font-weight-bold">Info</h6>
    <p class="mb-0">Ini adalah 4 QR Code pribadi (reusable) yang terikat ke akun Anda. QR Code ini dapat digunakan berulang kali untuk plat nomor yang terdaftar dan tidak akan dikenakan biaya menginap.</p>
</div>

<div class="row" id="personal-qr-container">
    {{-- Konten pertama kali dimuat oleh Blade (memanggil partial) --}}
    @include('member.personal_qrs.partials.qr_list', ['personalQrs' => $personalQrs])
</div>

@endsection

<!-- @push('scripts')
<script>
    // ===================================
    // 1. FUNGSI BARU UNTUK PRINT SPESIFIK
    // ===================================
    function printSpecificQr(elementId, pageTitle) {
        // Ambil HTML dari card yang ingin dicetak
        var printContent = document.getElementById(elementId).innerHTML;
        
        // Buat window pop-up baru
        var printWindow = window.open('', pageTitle, 'height=700,width=800');
        
        // Tulis HTML ke pop-up
        printWindow.document.write('<html><head><title>' + pageTitle + '</title>');
        
        // (Sangat Penting) Sertakan CSS Bootstrap agar tampilannya bagus
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
        
        // (Opsional) Style tambahan untuk print
        printWindow.document.write('<style>');
        printWindow.document.write('body { padding: 2rem; }');
        printWindow.document.write('img, svg { max-width: 100% !important; }'); // Pastikan QR pas
        printWindow.document.write('.card-body { border: 1px solid #eee; border-radius: 10px; }');
        printWindow.document.write('</style>');
        
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="card shadow h-100">' + printContent + '</div>'); // Bungkus lagi dengan card
        printWindow.document.write('</body></html>');
        
        printWindow.document.close(); // Selesaikan penulisan
        
        // Tunggu konten di-load, lalu panggil print
        printWindow.onload = function() {
            printWindow.focus();  // Fokus ke window baru
            printWindow.print();  // Panggil dialog print
            printWindow.close();  // Tutup setelah print
        };
    }


    // ===================================
    // 2. SCRIPT AJAX POLLING (Tetap sama)
    // ===================================
    function fetchPersonalQrData() {
        $.ajax({
            url: "{{ route('member.personal_qrs.index') }}", 
            type: 'GET',
            dataType: 'html',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(responseHtml) {
                var container = $('#personal-qr-container');
                container.html(responseHtml);
            },
            error: function(xhr, status, error) {
                console.error("Gagal memuat data QR Pribadi:", error);
            }
        });
    }

    $(document).ready(function() {
        // Polling setiap 10 detik
        setInterval(fetchPersonalQrData, 10000); 
    });
</script>
@endpush -->