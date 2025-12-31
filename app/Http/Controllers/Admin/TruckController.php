<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Truck;
use App\Models\User;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
class TruckController extends Controller
{
    /**
     * Menampilkan Form Tambah Truk Khusus Member Ini
     */
    public function createForMember(User $member)
    {
        return view('admin.trucks.create_for_member', compact('member'));
    }

    /**
     * Proses Simpan Truk & Generate QR Code Otomatis
     */
    public function storeForMember(Request $request, User $member)
    {
        // 1. Format Plat Nomor (Huruf Besar & Tanpa Spasi)
        $request->merge([
            'license_plate' => strtoupper(str_replace(' ', '', $request->license_plate)),
        ]);

        // 2. Validasi
        $request->validate([
            'license_plate' => 'required|string|max:20|unique:trucks,license_plate',
            'driver_name' => 'nullable|string|max:255',
        ], [
            'license_plate.unique' => 'Plat nomor ini sudah terdaftar di sistem.',
        ]);

        DB::beginTransaction();
        try {
            // A. Buat Data Truk
            $truck = Truck::create([
                'user_id' => $member->id, // PENTING: Link ke member yang dipilih
                'license_plate' => $request->license_plate,
                'driver_name' => $request->driver_name,
                'is_inside' => false, // Default di luar
            ]);

            // B. Buat QR Code Otomatis
            // Format Kode: TRK-{TANGGAL}-{ACAK} (Contoh: TRK-01012025-X9Y8)
            $customCode = now()->format('dmY') . '' . strtoupper(Str::random(2));
            
            QrCode::create([
                'truck_id' => $truck->id,
                'code' => $customCode,
                // 'status' => 'aktif', // Langsung aktif
                'is_approved' => true, // Auto approve karena Admin yang buat
            ]);

            DB::commit();
            
            // Redirect kembali ke halaman detail member
            return redirect()->route('admin.members.show', $member->id)
                             ->with('success', 'Truk berhasil ditambahkan dan QR Code telah dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }
    
}