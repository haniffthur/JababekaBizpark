<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Truck; // Import model Truk
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth
use Illuminate\Support\Facades\Gate; // Import Gate
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TruckController extends Controller
{
    /**
     * Menampilkan daftar truk MILIK user yang sedang login.
     * Rute: GET member/trucks
     */
    public function index(): View
    {
        // Ambil data truk HANYA milik user yang login
        $trucks = Auth::user()->trucks()->latest()->paginate(10);
        
        return view('member.trucks.index', compact('trucks'));
    }

    /**
     * Menampilkan form untuk menambah truk baru.
     * Rute: GET member/trucks/create
     */
    public function create(): View
    {
        return view('member.trucks.create');
    }

    /**
     * Menyimpan truk baru ke database.
     * Rute: POST member/trucks
     */
    public function store(Request $request)
    {
        // 1. BERSIHKAN FORMAT (Hapus Spasi & Uppercase)
        $request->merge([
            'license_plate' => strtoupper(str_replace(' ', '', $request->license_plate))
        ]);

        // 2. Validasi (Akan mengecek versi tanpa spasi)
        $request->validate([
            'license_plate' => 'required|string|unique:trucks,license_plate',
            'driver_name' => 'nullable|string|max:255',
        ]);

        // 3. Simpan
        $request->user()->trucks()->create($request->all());

        return redirect()->route('member.trucks.index')->with('success', 'Truk berhasil didaftarkan.');
    }

    /**
     * Menampilkan form untuk mengedit truk.
     * Rute: GET member/trucks/{truck}/edit
     */
    public function edit(Truck $truck): View
    {
        // Gunakan Gate 'manage-truck' yang sudah kita buat
        // Ini akan otomatis cek: (Apakah user admin ATAU user_id di truk == auth->id)
        if (Gate::denies('manage-truck', $truck)) {
            abort(403);
        }

        return view('member.trucks.edit', compact('truck'));
    }

    /**
     * Mengupdate data truk di database.
     * Rute: PUT/PATCH member/trucks/{truck}
     */
    public function update(Request $request, Truck $truck)
    {
        if ($truck->user_id !== Auth::id()) abort(403);

        // 1. BERSIHKAN FORMAT
        $request->merge([
            'license_plate' => strtoupper(str_replace(' ', '', $request->license_plate))
        ]);

        // 2. Validasi
        $request->validate([
            'license_plate' => ['required', 'string', \Illuminate\Validation\Rule::unique('trucks')->ignore($truck->id)],
            'driver_name' => 'nullable|string|max:255',
        ]);

        // 3. Update
        $truck->update($request->all());

        return redirect()->route('member.trucks.index')->with('success', 'Data truk diperbarui.');
    }

    /**
     * Menghapus truk dari database.
     * Rute: DELETE member/trucks/{truck}
     */
    public function destroy(Truck $truck): RedirectResponse
    {
        // Otorisasi pakai Gate
        if (Gate::denies('manage-truck', $truck)) {
            abort(403);
        }

        try {
            // Hapus truk
            // (Catatan: Relasi 'onDelete('cascade')' di migrasi 'qr_codes' 
            // akan otomatis menghapus semua QR code terkait truk ini)
            $truck->delete();

            return redirect()->route('member.trucks.index')
                             ->with('success', 'Truk berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus truk. Error: ' . $e->getMessage());
        }
    }
}