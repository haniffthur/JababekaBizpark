<!DOCTYPE html>
<html>
<head>
    <title>QR Code Pribadi - {{ $member->name }}</title>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f0;
            color: #1a1a1a;
            padding: 40px 30px;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 28px;
            border-bottom: 1.5px solid #e0e0d8;
            position: relative;
        }

        .page-header::after {
            content: '';
            display: block;
            width: 48px;
            height: 3px;
            background: #1a1a1a;
            margin: 0 auto;
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
        }

        .org-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 10px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #1a1a1a;
        }

        .page-subtitle {
            margin-top: 8px;
            font-size: 14px;
            color: #666;
            font-weight: 400;
        }

        .page-subtitle strong {
            color: #1a1a1a;
            font-weight: 600;
        }

        /* ── CLEARFIX ── */
        .clearfix::after { content: ''; display: table; clear: both; }

        /* ── CARD ── */
        .card {
            background: #ffffff;
            border: 1px solid #e8e8e2;
            border-radius: 12px;
            padding: 28px 24px;
            text-align: center;
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            display: block;
            height: 4px;
            background: #1a1a1a;
            position: absolute;
            top: 0; left: 0; right: 0;
            border-radius: 12px 12px 0 0;
        }

        .vehicle-name {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 20px;
        }

        /* Cari dan ganti .qr-wrapper menjadi ini */
.qr-wrapper { 
    margin: 15px 0; 
    text-align: center; 
    width: 100%; 
}
.qr-wrapper img {
    display: block;
    margin: 0 auto;
    width: 80%; /* Batasi maksimal lebar agar tidak menabrak garis putus-putus */
    height: auto;
}

        .plate-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 6px;
        }

        .plate-number {
            font-family: 'DM Mono', monospace;
            font-size: 22px;
            font-weight: 500;
            letter-spacing: 3px;
            color: #1a1a1a;
            background: #f5f5f0;
            border: 1.5px solid #e0e0d8;
            border-radius: 6px;
            padding: 8px 18px;
            display: inline-block;
            margin-bottom: 14px;
        }

        .code-text {
            font-family: 'DM Mono', monospace;
            font-size: 9.5px;
            color: #bbb;
            letter-spacing: 0.5px;
            word-break: break-all;
        }

        /* ── FOOTER ── */
        .page-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e0e0d8;
            text-align: center;
            font-size: 10px;
            color: #bbb;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

    <div class="page-header">
        <div class="org-label">Dokumen Akses Kendaraan</div>
        <div class="page-title">QR Code Kendaraan Pribadi</div>
        <div class="page-subtitle">Member: <strong>{{ $member->name }}</strong></div>
    </div>

    <div style="text-align: center;">
        @foreach($personalQrs as $index => $qr)
            <div style="
                display: inline-block;
                width: 44%;
                margin: 0 10px 18px 10px;
                vertical-align: top;
                page-break-inside: avoid;
            ">
                <div class="card">
                    <div class="vehicle-name">{{ $qr->name }}</div>

                    <div class="qr-wrapper">
                        {{-- Menggunakan format PNG langsung dari Milon/Barcode (Sangat aman untuk DOMPDF) --}}
<img src="data:image/png;base64,{!! DNS1D::getBarcodePNG($qr->code, 'C128', 2, 60) !!}" alt="Barcode">
                    </div>

                    <div class="plate-label">Plat Nomor</div>
                    <div class="plate-number">{{ $qr->license_plate }}</div>

                    <!-- <div class="code-text">{{ $qr->code }}</div> -->
                </div>
            </div>
        @endforeach
    </div>

    <div class="page-footer">
        Dokumen ini digenerate secara otomatis &mdash; {{ $member->name }}
    </div>

</body>
</html>