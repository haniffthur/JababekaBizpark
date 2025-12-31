<!DOCTYPE html>
<html>
<head>
    <title>QR Code Pribadi - {{ $member->name }}</title>
    <style>
        body { font-family: sans-serif; }
        .container { width: 100%; }
        .card-wrapper { 
            width: 45%; 
            float: left; 
            margin: 10px; 
            border: 1px dashed #333; 
            padding: 10px;
            page-break-inside: avoid;
        }
        .card-title { font-weight: bold; font-size: 14px; text-transform: uppercase; color: #4e73df; }
        .plate-number { font-size: 18px; font-weight: bold; margin-top: 5px; }
        .qr-img { margin-top: 10px; }
    </style>
</head>
<body>
    <div style="text-align: center; margin-bottom: 20px; clear: both;">
        <h2>QR Code Kendaraan Pribadi</h2>
        <p>Member: {{ $member->name }}</p>
    </div>

    <div class="container">
        @foreach($personalQrs as $qr)
            <div class="card-wrapper">
                <div style="text-align: center;">
                    <div class="card-title">{{ $qr->name }}</div>
                    <div class="qr-img">
                         <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(130)->generate($qr->code)) !!} ">
                    </div>
                    <div class="plate-number">{{ $qr->license_plate }}</div>
                    <small>{{ $qr->code }}</small>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>