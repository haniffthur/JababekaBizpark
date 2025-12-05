<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalQr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PersonalQrController extends Controller
{
    /**
     * HALAMAN UTAMA: Menampilkan Daftar Member & Jumlah QR mereka.
     * Rute: GET /admin/personal-qrs
     */
    public function index(Request $request): View
    {
        // Ambil user yang role-nya 'member'
        // withCount('personalQrs') akan menghitung jumlah QR secara otomatis
        // Fitur pencarian sederhana (opsional)
        $query = User::where('role', 'member');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->withCount('personalQrs')
                         ->latest()
                         ->paginate(10);

        return view('admin.personal_qrs.index', compact('members'));
    }

    /**
     * HALAMAN DETAIL: Menampilkan list QR milik satu member tertentu.
     * Rute: GET /admin/personal-qrs/member/{user}
     */
    public function showMemberQrs(User $user): View
    {
        // Pastikan yang dilihat adalah member
        if ($user->role !== 'member') {
            abort(404);
        }

        // Ambil semua QR milik user ini
        $personalQrs = $user->personalQrs()->latest()->get();

        return view('admin.personal_qrs.show_member', compact('user', 'personalQrs'));
    }

    /**
     * HALAMAN EDIT: Form edit satu QR.
     * Rute: GET /admin/personal-qrs/{personalQr}/edit
     */
    public function edit(PersonalQr $personalQr): View
    {
        return view('admin.personal_qrs.edit', compact('personalQr'));
    }

    /**
     * PROSES UPDATE: Menyimpan perubahan QR.
     * Rute: PUT /admin/personal-qrs/{personalQr}
     */
    public function update(Request $request, PersonalQr $personalQr): RedirectResponse
    {
        // 1. Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // Nama Slot (misal: Mobil Alphard)
            'license_plate' => [
                'required', 
                'string', 
                'max:20',
                // Plat nomor harus unik di tabel personal_qrs, KECUALI punya dia sendiri
                Rule::unique('personal_qrs')->ignore($personalQr->id)
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // 2. Update Data Dasar
        $personalQr->name = $request->name;
        $personalQr->license_plate = $request->license_plate;

        // 3. Cek Opsi Regenerasi Kode (Jika Admin mencentang checkbox)
        if ($request->has('regenerate_code')) {
            // Format: dd/mm/yyyy/8DIGITACAK (Contoh: 28/11/2025/A1B2C3D4)
            $personalQr->code = now()->format('dmY') . '' . strtoupper(Str::random(2));
            
            // Reset status ke 'baru' (karena kode berubah, harus check-in ulang)
            $personalQr->status = 'baru';
        }

        $personalQr->save();

        // 4. Redirect kembali ke halaman detail member tersebut
        return redirect()->route('admin.personal-qrs.member', $personalQr->user_id)
                         ->with('success', 'QR Pribadi berhasil diperbarui.');
    }

    /**
     * HAPUS QR (Opsional, jika diperlukan)
     */
    public function destroy(PersonalQr $personalQr): RedirectResponse
    {
        $userId = $personalQr->user_id;
        $personalQr->delete();

        return redirect()->route('admin.personal-qrs.member', $userId)
                         ->with('success', 'QR Pribadi berhasil dihapus.');
    }
}