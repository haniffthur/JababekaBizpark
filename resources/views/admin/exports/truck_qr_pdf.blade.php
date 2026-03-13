<!DOCTYPE html>
<html>
<head>
    <title>QR Code Truk - {{ $member->name }}</title>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f0;
            color: #1a1a1a;
            padding: 40px 36px;
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

        .page-meta {
            margin-top: 10px;
            font-size: 13.5px;
            color: #666;
        }

        .page-meta strong {
            color: #1a1a1a;
            font-weight: 600;
        }

        /* ── TRUCK CARD ── */
        .truck-card {
            background: #ffffff;
            border: 1px solid #e8e8e2;
            border-radius: 14px;
            margin-bottom: 20px;
            page-break-inside: avoid;
            overflow: hidden;
            display: table;
            width: 100%;
        }

        /* Accent stripe on left */
        .card-stripe {
            display: table-cell;
            width: 6px;
            background: #1a1a1a;
            border-radius: 14px 0 0 14px;
        }

        /* QR Section */
        .card-qr {
            display: table-cell;
            width: 260px;
            vertical-align: middle;
            text-align: center;
            padding: 30px 24px;
            border-right: 1px solid #f0f0ea;
        }

        .qr-frame {
            display: inline-block;
            padding: 16px;
            background: #fafaf8;
            border: 1px solid #e8e8e2;
            border-radius: 12px;
            margin-bottom: 14px;
        }

        .qr-frame img {
            display: block;
            width: 200px;
            height: 200px;
        }

        .qr-code-text {
            font-family: 'DM Mono', monospace;
            font-size: 9px;
            color: #c0c0b8;
            letter-spacing: 0.3px;
            word-break: break-all;
            max-width: 210px;
            margin: 0 auto;
        }

        /* Info Section */
        .card-info {
            display: table-cell;
            vertical-align: middle;
            padding: 32px 36px;
        }

        .card-badge {
            display: inline-block;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #1a1a1a;
            background: #f0f0ea;
            border-radius: 4px;
            padding: 4px 10px;
            margin-bottom: 20px;
        }

        .info-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 8px;
        }

        .plate-box {
            display: inline-block;
            font-family: 'DM Mono', monospace;
            font-size: 30px;
            font-weight: 500;
            letter-spacing: 4px;
            color: #1a1a1a;
            background: #f5f5f0;
            border: 2px solid #1a1a1a;
            border-radius: 8px;
            padding: 10px 24px;
            margin-bottom: 28px;
        }

        .divider {
            width: 32px;
            height: 2px;
            background: #e0e0d8;
            margin-bottom: 20px;
        }

        .driver-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 6px;
        }

        .driver-name {
            font-size: 17px;
            font-weight: 600;
            color: #1a1a1a;
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
        <div class="page-title">Daftar QR Code Truk</div>
        <div class="page-meta">
            Pemilik: <strong>{{ $member->name }}</strong> &nbsp;&middot;&nbsp; {{ $member->email }}
        </div>
    </div>

    @foreach($trucks as $truck)
        @if($truck->qrCode)
        <div class="truck-card">
            <div class="card-stripe"></div>

            <div class="card-qr">
                <div class="qr-frame">
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(200)->generate($truck->qrCode->code)) !!} ">
                </div>
                <div class="qr-code-text">{{ $truck->qrCode->code }}</div>
            </div>

            <div class="card-info">
                <div class="card-badge">Kartu Akses Truk</div>

                <div class="info-label">Plat Nomor</div>
                <div class="plate-box">{{ $truck->license_plate }}</div>

                <div class="divider"></div>

                <div class="driver-label">Nama Supir</div>
                <div class="driver-name">{{ $truck->driver_name ?? '—' }}</div>
            </div>
        </div>
        @endif
    @endforeach

    <div class="page-footer">
        Dokumen ini digenerate secara otomatis &mdash; {{ $member->name }}
    </div>

</body>
</html>