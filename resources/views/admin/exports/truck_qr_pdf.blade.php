<!DOCTYPE html>
<html>
<head>
    <title>QR Code Truk - {{ $member->name }}</title>
    <style>
        body { font-family: sans-serif; }
        .card {
            border: 2px solid #000;
            padding: 20px;
            margin-bottom: 20px;
            width: 100%;
            page-break-inside: avoid;
        }
        .header { text-align: center; border-bottom: 1px solid #ccc; margin-bottom: 10px; }
        .content { display: table; width: 100%; }
        .qr-box { display: table-cell; width: 30%; text-align: center; vertical-align: middle; }
        .info-box { display: table-cell; width: 70%; padding-left: 20px; vertical-align: middle; }
        h2 { margin: 0; }
        .plate { font-size: 24px; font-weight: bold; background: #eee; padding: 5px 10px; display: inline-block; border: 1px solid #999; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 30px;">
        <h1>Daftar QR Code Truk</h1>
        <p>Pemilik: <strong>{{ $member->name }}</strong> ({{ $member->email }})</p>
    </div>

    @foreach($trucks as $truck)
        @if($truck->qrCode)
        <div class="card">
            <div class="content">
                <div class="qr-box">
                    {{-- Generate QR Code sebagai Image Base64 agar muncul di PDF --}}
                    <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(120)->generate($truck->qrCode->code)) !!} ">
                    <br>
                    <small>{{ $truck->qrCode->code }}</small>
                </div>
                <div class="info-box">
                    <h3>KARTU AKSES TRUK</h3>
                    Plat Nomor:
                    <br>
                    <div class="plate">{{ $truck->license_plate }}</div>
                    <br><br>
                    Supir: {{ $truck->driver_name ?? '-' }}
                </div>
            </div>
        </div>
        @endif
    @endforeach
</body>
</html>