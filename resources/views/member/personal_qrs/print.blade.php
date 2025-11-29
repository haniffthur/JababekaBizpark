{{-- resources/views/member/personal_qrs/print.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR - {{ $qrcode->license_plate }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f0f0f0; /* Latar belakang abu-abu saat di layar */
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .print-card {
            width: 450px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .print-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 1rem 1.5rem;
        }
        .print-body {
            padding: 2rem;
        }
        
        /* 3. CSS @media print (KUNCI UTAMA) */
        @media print {
            /* Sembunyikan semua elemen KECUALI yang kita mau cetak */
            body > *:not(.print-container) {
                display: none;
            }
            body {
                background-color: #fff; /* Hapus latar belakang saat print */
                padding: 0;
                margin: 0;
            }
            .print-container {
                display: block;
                width: 100%;
                box-shadow: none;
                border: none;
            }
            .print-card {
                box-shadow: none;
                border: none;
                width: 100%;
            }
            /* Sembunyikan tombol "Cetak Ulang" saat print */
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="print-container">
        <div class="print-card">
            <div class="print-header">
                <h5 class="m-0 text-primary">{{ $qrcode->name }} - {{ $qrcode->license_plate }}</h5>
            </div>
            <div class="print-body">
                <div class="row g-3 align-items-center">
                    <div class="col-7 text-center">
                        @QrCode($qrcode->code) {{-- Panggil Blade Directive --}}
                        <code class="d-block mt-2">{{ $qrcode->code }}</code>
                    </div>
                    <div class="col-5">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Plat:</span>
                                <strong>{{ $qrcode->license_plate }}</strong>
                            </li>
                         <li class="list-group-item d-flex justify-content-between align-items-center px-0">
    <span>Member:</span>
    {{-- Ambil nama user dari relasi $qrcode->user --}}
    <strong>{{ $qrcode->user->name ?? 'N/A' }}</strong>
</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <p class="text-center text-muted small mb-0">
                    GudangJababeka Gate System
                </p>
                <div class="text-center mt-3 no-print">
                    <button class="btn btn-primary" onclick="window.print();">
                        Cetak Ulang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print(); // Otomatis panggil dialog print saat halaman dimuat
        };
    </script>
</body>
</html>