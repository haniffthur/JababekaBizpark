<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterQr;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        // Generate QR Code sebagai PNG
        $image = QrCode::format('png')
                 ->size(500)
                 ->margin(2)
                 ->errorCorrection('H')
                 ->generate($masterQr->code);

        $filename = 'QR_MASTER_' . str_replace(' ', '_', strtoupper($masterQr->name)) . '.png';

        return response($image)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
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