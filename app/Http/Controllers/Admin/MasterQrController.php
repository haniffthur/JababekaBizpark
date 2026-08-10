<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterQr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Milon\Barcode\DNS1D;

class MasterQrController extends Controller
{
    /**
     * Menampilkan daftar Master QR.
     */
    public function index()
    {
        $masterQrs = MasterQr::latest()->get();
        return view('admin.master_qrs.index', compact('masterQrs'));
    }

    /**
     * Menyimpan Master QR baru.
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        // Format SAMA dengan QR lain: Tanggal + Random String
        // Contoh: 11042026A1B2
        $code = now()->format('dmY') . strtoupper(Str::random(2)); 

        MasterQr::create([
            'name' => $request->name,
            'code' => $code,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Master QR berhasil dibuat.');
    }


public function downloadQr(MasterQr $masterQr)
{
    $barcode = new DNS1D();

    // Generate barcode Code 128
    $barcodeBase64 = $barcode->getBarcodePNG(
        $masterQr->code,
        'C128',
        2,
        60
    );

    // Decode barcode PNG
    $barcodeImage = imagecreatefromstring(
        base64_decode($barcodeBase64)
    );

    // Ukuran barcode
    $barcodeWidth = imagesx($barcodeImage);
    $barcodeHeight = imagesy($barcodeImage);

    // Padding
    $paddingX = 40;
    $paddingTop = 35;
    $paddingBottom = 45;

    // Ukuran canvas
    $canvasWidth = $barcodeWidth + ($paddingX * 2);
    $canvasHeight = $barcodeHeight + $paddingTop + $paddingBottom;

    // Buat canvas putih
    $canvas = imagecreatetruecolor(
        $canvasWidth,
        $canvasHeight
    );

    // Warna
    $white = imagecolorallocate(
        $canvas,
        255,
        255,
        255
    );

    $borderColor = imagecolorallocate(
        $canvas,
        220,
        220,
        220
    );

    // Background putih
    imagefill(
        $canvas,
        0,
        0,
        $white
    );

    // Border
    imagerectangle(
        $canvas,
        0,
        0,
        $canvasWidth - 1,
        $canvasHeight - 1,
        $borderColor
    );

    // Tempel barcode di tengah
    imagecopy(
        $canvas,
        $barcodeImage,
        $paddingX,
        $paddingTop,
        0,
        0,
        $barcodeWidth,
        $barcodeHeight
    );

    // Bersihkan resource barcode
    imagedestroy($barcodeImage);

    // Nama file
    $filename = 'BARCODE_MASTER_' .
        str_replace(
            ' ',
            '_',
            strtoupper($masterQr->name)
        ) .
        '.png';

    // Output PNG
    ob_start();

    imagepng(
        $canvas,
        null,
        6
    );

    $image = ob_get_clean();

    // Bersihkan canvas
    imagedestroy($canvas);

    return response($image)
        ->header('Content-Type', 'image/png')
        ->header(
            'Content-Disposition',
            'attachment; filename="' . $filename . '"'
        );
}

    /**
     * Mengupdate nama Master QR.
     */
    public function update(Request $request, MasterQr $masterQr): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $masterQr->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.master-qrs.index')
                         ->with('success', 'Master QR berhasil diperbarui.');
    }

    /**
     * Menghapus Master QR.
     */
    public function destroy(MasterQr $masterQr): RedirectResponse
    {
        $masterQr->delete();

        return redirect()->route('admin.master-qrs.index')
                         ->with('success', 'Master QR berhasil dihapus.');
    }

    /**
     * Mengaktifkan/Menonaktifkan Master QR.
     */
    public function toggleStatus(MasterQr $masterQr): RedirectResponse
    {
        $masterQr->update([
            'is_active' => !$masterQr->is_active
        ]);

        $status = $masterQr->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        return redirect()->route('admin.master-qrs.index')
                         ->with('success', "Master QR berhasil {$status}.");
    }
}