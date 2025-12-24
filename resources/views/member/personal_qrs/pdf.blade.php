{{-- resources/views/member/personal_qrs/pdf.blade.php --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>QR Code - {{ $qrcode->license_plate }}</title>
    
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4e73df;
        }
        .content {
            margin-top: 30px;
        }
        .info-box {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fc;
        }
        .info-box strong {
            display: inline-block;
            width: 100px;
            color: #5a5c69;
        }
        .info-box p {
            margin: 5px 0;
        }
        .qr-code-box {
            text-align: center;
            margin-top: 30px;
        }
        .qr-code-box img {
            width: 300px;
            height: 300px;
        }
        .qr-code-box code {
            display: block;
            margin-top: 10px;
            font-size: 1.1rem;
            color: #e74a3b;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #858796;
            border-top: 1px solid #f0f0f0;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header">
            <h1>GudangJababeka</h1>
            <p>QR Code Pribadi</p>
        </div>

        <div class="content">
            <div class="info-box">
                <p><strong>Member:</strong> {{ $qrcode->user->name ?? 'N/A' }}</p>
                <p><strong>Plat:</strong> {{ $qrcode->license_plate }}</p>
                <p><strong>Slot:</strong> {{ $qrcode->name }}</p>
            </div>

            <div class="qr-code-box">
                {{-- Ini adalah gambar PNG Base64 yang kita buat di controller --}}
                <img src="{{ $qrCodeImage }}" alt="QR Code">
                <!-- <code>{{ $qrcode->code }}</code> -->
            </div>
        </div>

        <div class="footer">
            <p>Copyright &copy; GudangJababeka {{ date('Y') }}</s_p>
        </div>

    </div>
</body>
</html>