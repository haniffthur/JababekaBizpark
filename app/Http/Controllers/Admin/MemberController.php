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
        // ... (Logika store tetap sama seperti sebelumnya, copy dari kode yang sudah jalan) ...
        // Agar tidak kepanjangan, saya asumsikan kode store kamu sudah aman.
        // Jika butuh kode store lengkap lagi, kabari ya.
        
        // --- Versi Singkat untuk Store (Paste kode lama kamu di sini) ---
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'ipl_status' => 'paid',
            'plate_1' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
            'plate_2' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
            'plate_3' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
            'plate_4' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
        ]);

        if ($validator->fails()) return back()->withErrors($validator)->withInput();

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'member',
            ]);

            $plates = [
                'Pribadi 1' => $request->plate_1,
                'Pribadi 2' => $request->plate_2,
                'Pribadi 3' => $request->plate_3,
                'Pribadi 4' => $request->plate_4,
            ];

            foreach ($plates as $name => $plate) {
                if (!empty($plate)) {
                    // Format Baru: Tanggal/Acak
                    $customCode = now()->format('dmY') . '' . strtoupper(Str::random(2));
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
            return redirect()->route('admin.members.index')->with('success', 'Member berhasil ditambahkan.');
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
        
        // Load QR Pribadi agar bisa ditampilkan di form edit
        $member->load('personalQrs');
        
        return view('admin.members.edit', compact('member'));
    }

    /**
     * UPDATE MEMBER (UPDATE: Handle Plat Nomor)
     */
    public function update(Request $request, User $member): RedirectResponse
    {
        // 1. Validasi Dasar
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($member->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'ipl_status' => 'required|in:paid,unpaid',
        ];

        // Validasi Plat (Manual Check nanti di loop agar bisa ignore ID sendiri)
        // Kita validasi formatnya saja di sini
        $rules['plate_1'] = 'nullable|string|max:20';
        $rules['plate_2'] = 'nullable|string|max:20';
        $rules['plate_3'] = 'nullable|string|max:20';
        $rules['plate_4'] = 'nullable|string|max:20';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // 2. Update User
            $member->name = $request->name;
            $member->email = $request->email;
            $member->ipl_status = $request->ipl_status;
            if ($request->filled('password')) {
                $member->password = Hash::make($request->password);
            }
            $member->save();

            // 3. Update / Create QR Pribadi
            $platesInput = [
                'Pribadi 1' => $request->plate_1,
                'Pribadi 2' => $request->plate_2,
                'Pribadi 3' => $request->plate_3,
                'Pribadi 4' => $request->plate_4,
            ];

            foreach ($platesInput as $slotName => $plateNumber) {
                // Cari apakah slot ini sudah ada di database
                $existingQr = PersonalQr::where('user_id', $member->id)
                                        ->where('name', $slotName)
                                        ->first();

                if (!empty($plateNumber)) {
                    // Cek Unik (Plat tidak boleh dipakai orang lain, atau slot lain)
                    $cekDuplikat = PersonalQr::where('license_plate', $plateNumber)
                                             ->where('id', '!=', $existingQr ? $existingQr->id : 0)
                                             ->exists();
                    
                    if ($cekDuplikat) {
                        throw new \Exception("Plat nomor $plateNumber sudah digunakan oleh member lain.");
                    }

                    if ($existingQr) {
                        // UPDATE: Jika sudah ada, update platnya saja
                        $existingQr->license_plate = $plateNumber;
                        $existingQr->save();
                    } else {
                        // CREATE: Jika belum ada, buat baru
                        $customCode = now()->format('dmY') . '' . strtoupper(Str::random(2));
                        PersonalQr::create([
                            'user_id' => $member->id,
                            'name' => $slotName,
                            'license_plate' => $plateNumber,
                            'code' => $customCode,
                            'status' => 'baru',
                        ]);
                    }
                } else {
                    // DELETE: Jika input dikosongkan, hapus QR tersebut (Opsional)
                    // Jika kamu ingin plat bisa dihapus, uncomment baris bawah:
                    if ($existingQr) {
                         $existingQr->delete();
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.members.index')->with('success', 'Data member & QR berhasil diperbarui.');

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