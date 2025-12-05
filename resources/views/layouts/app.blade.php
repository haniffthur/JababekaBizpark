<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard</title>
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    @if(auth()->check() && auth()->user()->role == 'admin')

    {{-- Pastikan SweetAlert diload --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var globalLastCount = -1; // Variabel pelacak

        function checkGlobalNotifications() {
            $.ajax({
                url: "{{ route('admin.api.check.pending') }}", // Panggil API Ringan
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    var currentCount = response.count;
                    var badge = $('#sidebar-pending-badge'); // ID badge di sidebar

                    // A. Update Angka Sidebar
                    if (currentCount > 0) {
                        badge.text(currentCount).show();
                    } else {
                        badge.hide();
                    }

                    // B. Trigger Alert & Suara (Jika Bertambah)
                    if (globalLastCount !== -1 && currentCount > globalLastCount) {
                        
                        // Mainkan Suara
                        try {
                            var audio = document.getElementById('global-notif-sound');
                            audio.currentTime = 0;
                            audio.play().catch(e => console.log("Autoplay blocked"));
                        } catch(e) {}

                        // Tampilkan Alert
                        Swal.fire({
                            title: 'Permintaan Baru!',
                            text: 'Ada ' + (currentCount - globalLastCount) + ' permintaan QR Code baru.',
                            icon: 'info',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: true,
                            confirmButtonText: 'Lihat',
                            showCancelButton: true,
                            cancelButtonText: 'Tutup',
                            timer: 10000,
                            timerProgressBar: true,
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('admin.qr.approvals.index') }}";
                            }
                        });
                    }

                    globalLastCount = currentCount;
                },
                error: function(err) {
                    console.error("Gagal cek notifikasi:", err);
                }
            });
        }

        $(document).ready(function() {
            // Cek pertama kali
            checkGlobalNotifications();
            
            // Cek setiap 5 detik
            setInterval(checkGlobalNotifications, 5000);
        });
    </script>
@endif

{{-- Stack Scripts (Untuk script spesifik halaman lain) --}}
@stack('scripts')
    @stack('styles')
   <style>


 .sidebar-brand-text {
        font-size: 1rem;
        font-weight: bold;
        /* Hapus atau override text-dark jika ada konflik */
        /*color: #007bff; /* <-- UBAH ATAU TAMBAHKAN BARIS INI UNTUK WARNA BIRU */
        /* Anda bisa menggunakan warna lain seperti: */
        /* color: #1e3a8a;  (biru tua) */
         color: #4e73df;  (biru primary dari sb-admin-2) */
        /* color: blue;    (nama warna dasar) */
        white-space: nowrap;
    }
    /* Styling untuk elemen <img> logo */
    .sidebar-brand-logo {
        height: 35px;       /* Tinggi spesifik untuk logo. Sesuaikan jika logo Anda terlalu kecil/besar. */
        width: auto;        /* Biarkan lebar menyesuaikan secara proporsional agar gambar tidak pecah */
        max-height: 40px;   /* Batas tinggi maksimum */
        object-fit: contain; /* Penting! Memastikan seluruh gambar logo terlihat tanpa terpotong atau terdistorsi */
        margin-right: 2px; /* Memberi jarak antara logo dan teks "BinaTaruna" */
        vertical-align: middle; /* Membantu penempatan vertikal agar sejajar dengan teks */
    }

    /* Styling untuk teks "BinaTaruna" */
  

    /* Penyesuaian untuk kontainer ikon (div.sidebar-brand-icon) */
    /* Ini biasanya sudah ditangani oleh Bootstrap classes seperti d-flex, align-items-center */
    .sidebar-brand-icon {
        display: flex; /* Memastikan flexbox aktif untuk alignment */
        align-items: center; /* Memusatkan logo secara vertikal */
        justify-content: center; /* Memusatkan logo secara horizontal jika hanya ada logo di dalamnya */
        height: 100%; /* Memastikan kontainer mengambil tinggi penuh yang tersedia */
    }

    /* Penyesuaian opsional untuk kontainer keseluruhan sidebar-brand (elemen <a>) */
    /* Anda bisa mengubah padding atau tinggi jika ingin membuat area logo lebih ramping atau lebih luas */
    .sidebar-brand.py-4 {
        padding-top: 1rem !important;    /* Contoh: kurangi padding atas */
        padding-bottom: 1rem !important; /* Contoh: kurangi padding bawah */
        /* height: 55px; */ /* Atau atur tinggi tetap jika diinginkan */
    }
/* Shadow kanan */
.sidebar {
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    z-index: 1030;
}

/* Collapse container */
.sidebar .collapse-inner {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    background-color: #ffffff;
    padding: 0;
    overflow: hidden;
}

/* Item collapse */
.sidebar .collapse-item {
    display: block;
    width: 100%;
    padding: 0.65rem 1rem;
    color: #4e73df;
    font-size: 0.925rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    transition: background-color 0.2s ease, color 0.2s ease;
}

/* Hover & Active Style */
.sidebar .collapse-item:hover {
    background-color: #f1f3f9;
    color: #2e59d9;
    text-decoration: none;
}

.sidebar .collapse-item.active {
    background-color: #e8edfb;
    font-weight: 600;
    color: #224abe;
}
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery-easing/jquery.easing.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>
        <!-- Scripts dari halaman spesifik akan dimuat di sini -->
    @stack('scripts')
</body>
</html>
