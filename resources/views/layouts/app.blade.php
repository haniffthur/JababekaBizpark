<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GudangJababeka - Sistem Gate</title>

    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @stack('styles')
    
    <style>
        /* ==================================== */
        /* STYLE KHUSUS SWEETALERT2 (MODERN & CLEAN) */
        /* ==================================== */
        
        /* Mengatur tampilan umum popup: border radius, font, dan bayangan */
        div:where(.swal2-container) div:where(.swal2-popup) {
            border-radius: 12px !important; /* Sedikit lebih kecil dari 15px */
            font-family: 'Nunito', sans-serif !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important; /* Bayangan halus */
        }

        /* Mengatur warna dan padding judul */
        div:where(.swal2-container) h2:where(.swal2-title) {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            color: #3a3b45 !important; /* Warna teks yang lebih gelap/profesional */
            padding-top: 20px !important; /* Tambah padding atas */
        }

        /* Mengatur teks konten */
        div:where(.swal2-container) div:where(.swal2-html-container) {
            font-size: 1rem !important;
            color: #6c757d !important; /* Warna abu-abu yang lebih lembut */
        }

        /* Mengatur tombol konfirmasi */
        div:where(.swal2-container) button:where(.swal2-confirm) {
            background-color: #4e73df !important; /* Warna primary SB Admin 2 */
            color: white !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            transition: background-color 0.2s ease;
        }

        /* Mengatur tombol batal/cancel */
        div:where(.swal2-container) button:where(.swal2-cancel) {
            border-radius: 8px !important;
            background-color: #e4e4e4 !important; /* Warna abu-abu muda */
            color: #3a3b45 !important; /* Warna teks yang kontras */
            font-weight: 600 !important;
        }

        /* Mengatur ikon: agar lebih bulat dan menonjol */
        div:where(.swal2-container) .swal2-icon {
            margin-top: 15px !important;
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        @include('layouts.sidebar')

        <div id="content-wrapper" class="d-flex flex-column bg-white">
            <div id="content">
                @include('layouts.topbar')

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
            
            @include('layouts.footer')
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3000, // Hilang otomatis dalam 3 detik
                timerProgressBar: true,
                customClass: { popup: 'card-clean' }
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#e74a3b', // Warna merah
                customClass: { popup: 'card-clean' }
            });
        @endif
        
        @if($errors->any())
            // Menangkap error validasi form (misal: input kurang)
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                html: '<ul style="text-align: left;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                confirmButtonText: 'Perbaiki',
                confirmButtonColor: '#f6c23e', // Warna kuning
                customClass: { popup: 'card-clean' }
            });
        @endif
    </script>

    {{-- Stack Scripts --}}
    @stack('scripts')

    {{-- ========================================== --}}
    {{-- LOGIKA NOTIFIKASI GLOBAL (KHUSUS ADMIN) --}}
    {{-- ========================================== --}}
    @if(auth()->check() && auth()->user()->role == 'admin')
    <script>
        var lastTruckCount = -1;
    var lastPersonalCount = -1;

    function checkGlobalNotifications() {
        $.ajax({
            url: "{{ route('admin.api.check.pending') }}", // Pastikan route ini benar
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                
                // Update Badges Sidebar
                var truckBadge = $('#sidebar-pending-badge'); // Badge QR Truk
                var personalBadge = $('#sidebar-personal-pending-badge'); // Badge QR Pribadi (Buat ID ini di sidebar!)

                // Update angka badge Truk
                if (response.truck_count > 0) {
                    truckBadge.text(response.truck_count).show();
                } else {
                    truckBadge.hide();
                }
                
                // Update angka badge Pribadi (Asumsi kamu sudah tambah ID ini di sidebar)
                if (personalBadge.length && response.personal_count > 0) {
                    personalBadge.text(response.personal_count).show();
                } else if (personalBadge.length) {
                    personalBadge.hide();
                }

                // --- LOGIKA ALERT SPESIFIK ---

                // 1. Alert QR Truk
                if (lastTruckCount !== -1 && response.truck_count > lastTruckCount) {
                    Swal.fire({
                        title: 'Permintaan QR Truk!',
                        text: 'Ada permintaan QR Code TRUK baru.',
                        icon: 'info',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: true,
                        confirmButtonText: 'Lihat',
                        timer: 8000
                    }).then((result) => {
                        if (result.isConfirmed) window.location.href = "{{ route('admin.qr.approvals.index') }}";
                    });
                }

                // 2. Alert QR Pribadi
                if (lastPersonalCount !== -1 && response.personal_count > lastPersonalCount) {
                    Swal.fire({
                        title: 'Permintaan QR Pribadi!',
                        text: 'Ada permintaan QR PRIBADI baru.',
                        icon: 'warning', // Beda icon biar beda rasa
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: true,
                        confirmButtonText: 'Lihat',
                        timer: 8000
                    }).then((result) => {
                        if (result.isConfirmed) window.location.href = "{{ route('admin.personal-qrs.approvals') }}";
                    });
                }

                lastTruckCount = response.truck_count;
                lastPersonalCount = response.personal_count;
            }
        });
    }

        $(document).ready(function() {
            checkGlobalNotifications(); // Cek pas load
            setInterval(checkGlobalNotifications, 5000); // Cek tiap 5 detik
        });
    </script>
    @endif
    

    @stack('scripts')

</body>
</html>