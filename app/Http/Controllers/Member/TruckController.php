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
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'license_plate' => 'required|string|max:20|unique:trucks',
            'driver_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Buat truk baru dan otomatis kaitkan dengan user_id yang sedang login
        Auth::user()->trucks()->create($validator->validated());

        return redirect()->route('member.trucks.index')
                         ->with('success', 'Truk baru berhasil didaftarkan.');
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
    public function update(Request $request, Truck $truck): RedirectResponse
    {
        // Otorisasi pakai Gate
        if (Gate::denies('manage-truck', $truck)) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'license_plate' => [
                'required', 'string', 'max:20',
                Rule::unique('trucks')->ignore($truck->id), // Unik, kecuali diri sendiri
            ],
            'driver_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update truk
        $truck->update($validator->validated());

        return redirect()->route('member.trucks.index')
                         ->with('success', 'Data truk berhasil diperbarui.');
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