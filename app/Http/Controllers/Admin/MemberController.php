<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\PersonalQr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = User::where('role', 'member')->latest()->paginate(10);
        return view('admin.members.index', compact('members'));
    }

    public function create(): View
    {
        return view('admin.members.create');
    }

   public function store(Request $request): RedirectResponse
{
    // 1. BERSIHKAN FORMAT (4 Input Sekaligus)
    // Kita paksa uppercase dan hapus spasi sebelum validasi
    $request->merge([
        'plate_1' => $request->plate_1 ? strtoupper(str_replace(' ', '', $request->plate_1)) : null,
        'plate_2' => $request->plate_2 ? strtoupper(str_replace(' ', '', $request->plate_2)) : null,
        'plate_3' => $request->plate_3 ? strtoupper(str_replace(' ', '', $request->plate_3)) : null,
        'plate_4' => $request->plate_4 ? strtoupper(str_replace(' ', '', $request->plate_4)) : null,
    ]);

    // 2. VALIDASI (Ubah ke 'required' agar wajib diisi semua)
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8', // Hapus confirmed jika tidak ada input password_confirmation di view
        // 'ipl_status' => 'paid', // Ini bukan aturan validasi, hapus dari sini atau masukkan ke logic create
        
        // WAJIB DIISI (REQUIRED)
        'plate_1' => 'required|string|max:20|unique:personal_qrs,license_plate',
        'plate_2' => 'required|string|max:20|unique:personal_qrs,license_plate',
        'plate_3' => 'required|string|max:20|unique:personal_qrs,license_plate',
        'plate_4' => 'required|string|max:20|unique:personal_qrs,license_plate',
    ], [
        // Custom Error Message (Opsional)
        'plate_1.required' => 'Plat nomor ke-1 wajib diisi.',
        'plate_1.unique' => 'Plat nomor ke-1 sudah terdaftar.',
        // dst...
    ]);

    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    DB::beginTransaction();
    try {
        // Simpan User
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
            'ipl_status' => 'paid', // Set default paid sesuai request sebelumnya
        ]);

        // Mapping Data Plat
        $plates = [
            'Pribadi 1' => $request->plate_1,
            'Pribadi 2' => $request->plate_2,
            'Pribadi 3' => $request->plate_3,
            'Pribadi 4' => $request->plate_4,
        ];

        // Loop Simpan QR
        foreach ($plates as $name => $plate) {
            // Karena wajib (required), kita tidak perlu cek !empty lagi, tapi untuk keamanan biarkan saja
            if (!empty($plate)) {
                $customCode = now()->format('dmY') . strtoupper(Str::random(2)); 

                PersonalQr::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'license_plate' => $plate,
                    'code' => $customCode,
                    'status' => 'baru',
                ]);
            }
        }

        DB::commit();
        return redirect()->route('admin.members.index')->with('success', 'Member dan 4 QR Code berhasil ditambahkan.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
    }
}

    public function show(User $member): View
    {
        if ($member->role === 'admin') abort(404);
        $member->load('trucks', 'billings', 'personalQrs'); // Load personalQrs juga
        return view('admin.members.show', compact('member'));
    }

    /**
     * EDIT MEMBER (UPDATE: Load Relasi QR)
     */
   public function edit(User $member): View
    {
        if ($member->role === 'admin') abort(404);
        
        // Load semua QR milik member
        $member->load('personalQrs');

        // Daftar nama slot standar
        $standardNames = ['Pribadi 1', 'Pribadi 2', 'Pribadi 3', 'Pribadi 4'];

        // 1. Ambil QR Standar (Slot 1-4)
        $standardQrs = $member->personalQrs->whereIn('name', $standardNames);

        // 2. Ambil QR Tambahan (Yang request manual / selain slot standar)
        // Ini yang akan kita tampilkan di bagian bawah view
        $extraQrs = $member->personalQrs->whereNotIn('name', $standardNames);
        
        return view('admin.members.edit', compact('member', 'standardQrs', 'extraQrs'));
    }

    /**
     * UPDATE MEMBER
     * Menangani update data diri, 4 plat wajib, DAN plat tambahan (extra)
     */
    public function update(Request $request, User $member): RedirectResponse
    {
        // A. FORMAT INPUT (Uppercase & Hapus Spasi)
        $request->merge([
            'plate_1' => $request->plate_1 ? strtoupper(str_replace(' ', '', $request->plate_1)) : null,
            'plate_2' => $request->plate_2 ? strtoupper(str_replace(' ', '', $request->plate_2)) : null,
            'plate_3' => $request->plate_3 ? strtoupper(str_replace(' ', '', $request->plate_3)) : null,
            'plate_4' => $request->plate_4 ? strtoupper(str_replace(' ', '', $request->plate_4)) : null,
        ]);

        // Format juga inputan extra_plates jika ada
        if ($request->has('extra_plates')) {
            $formattedExtras = [];
            foreach ($request->extra_plates as $id => $plate) {
                $formattedExtras[$id] = strtoupper(str_replace(' ', '', $plate));
            }
            $request->merge(['extra_plates' => $formattedExtras]);
        }

        // B. VALIDASI
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($member->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'ipl_status' => 'required|in:paid,unpaid',
            // 4 Plat Wajib
            'plate_1' => 'required|string|max:20',
            'plate_2' => 'required|string|max:20',
            'plate_3' => 'required|string|max:20',
            'plate_4' => 'required|string|max:20',
        ];

        // Validasi Plat Tambahan (Jika ada yang diedit)
        if ($request->has('extra_plates')) {
            foreach ($request->extra_plates as $id => $plate) {
                $rules["extra_plates.$id"] = 'required|string|max:20';
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::beginTransaction();
        try {
            // 1. Update User Data
            $member->name = $request->name;
            $member->email = $request->email;
            $member->ipl_status = $request->ipl_status;
            if ($request->filled('password')) {
                $member->password = Hash::make($request->password);
            }
            $member->save();

            // ---------------------------------------------------------
            // 2. Update 4 Slot Utama (Wajib) + LOGIK RESET QR
            // ---------------------------------------------------------
            $standardPlates = [
                1 => $request->plate_1, // Key kita ubah jadi angka 1-4 biar mudah dicocokkan
                2 => $request->plate_2,
                3 => $request->plate_3,
                4 => $request->plate_4,
            ];

            // Ambil array ID yang minta di-reset (jika ada)
            $regenStandards = $request->input('regen_standard', []); // Contoh isi: [1, 3]

            foreach ($standardPlates as $index => $newPlate) {
                $slotName = "Pribadi $index";
                
                $existingQr = PersonalQr::where('user_id', $member->id)->where('name', $slotName)->first();
                $qrId = $existingQr ? $existingQr->id : 0;

                // Cek Unik Global
                $isDuplicate = PersonalQr::where('license_plate', $newPlate)->where('id', '!=', $qrId)->exists();
                if ($isDuplicate) throw new \Exception("Plat nomor $newPlate sudah digunakan member lain.");

                if ($existingQr) {
                    // Cek apakah Admin mencentang Reset untuk slot ini?
                    // in_array(1, [1, 3]) -> true
                    if (in_array($index, $regenStandards)) {
                        // GENERATE KODE BARU
                        $newCode = now()->format('dmY') . strtoupper(Str::random(2));
                        $existingQr->update([
                            'license_plate' => $newPlate,
                            'code' => $newCode,
                            'status' => 'baru' // Reset status jadi baru (harus tap in lagi)
                        ]);
                    } else {
                        // Cuma update plat nomor
                        $existingQr->update(['license_plate' => $newPlate]);
                    }
                } else {
                    // Buat Baru (Otomatis code baru)
                    PersonalQr::create([
                        'user_id' => $member->id,
                        'name' => $slotName,
                        'license_plate' => $newPlate,
                        'code' => now()->format('dmY') . strtoupper(Str::random(2)),
                        'status' => 'baru',
                    ]);
                }
            }

            // ---------------------------------------------------------
            // 3. UPDATE QR TAMBAHAN + LOGIK RESET QR
            // ---------------------------------------------------------
            if ($request->has('extra_plates')) {
                // Ambil array ID extra yang minta di-reset
                $regenExtras = $request->input('regen_extras', []); // Contoh isi: [15, 20]

                foreach ($request->extra_plates as $qrId => $newPlate) {
                    // Cek Duplikat
                    $isDuplicate = PersonalQr::where('license_plate', $newPlate)->where('id', '!=', $qrId)->exists();
                    if ($isDuplicate) throw new \Exception("Plat tambahan $newPlate sudah digunakan member lain.");

                    $qrToUpdate = PersonalQr::where('id', $qrId)->where('user_id', $member->id)->first();
                    
                    if ($qrToUpdate) {
                        $updateData = ['license_plate' => $newPlate];

                        // Cek apakah dicentang reset?
                        if (in_array($qrId, $regenExtras)) {
                            $updateData['code'] = now()->format('dmY') . strtoupper(Str::random(2));
                            $updateData['status'] = 'baru';
                        }

                        $qrToUpdate->update($updateData);
                    }
                }
            }

            // 4. HAPUS QR TAMBAHAN
            if ($request->has('delete_extras')) {
                PersonalQr::whereIn('id', $request->delete_extras)->where('user_id', $member->id)->delete();
            }

            DB::commit();
            return redirect()->route('admin.members.index')->with('success', 'Data member berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(User $member): RedirectResponse
    {
        // ... (Kode destroy sama) ...
        try {
            if ($member->role === 'admin' || $member->id === auth()->id()) {
                 return back()->with('error', 'Anda tidak bisa menghapus akun ini.');
            }
            $member->delete();
            return redirect()->route('admin.members.index')->with('success', 'Member berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
    
    public function getMemberData(): JsonResponse
    {
        $members = User::where('role', 'member')->latest()->take(10)->get();
        return response()->json(['data' => $members]);
    }
}