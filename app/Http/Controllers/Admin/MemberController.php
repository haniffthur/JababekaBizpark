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
    /**
     * Menampilkan daftar semua member.
     */
    public function index(): View
    {
        $members = User::where('role', 'member')->latest()->paginate(10);
        return view('admin.members.index', compact('members'));
    }

    /**
     * Menampilkan form untuk menambah member baru.
     */
    public function create(): View
    {
        return view('admin.members.create');
    }

    /**
     * Menyimpan member baru ke database.
     */
   public function store(Request $request): RedirectResponse
{
    // 1. Validasi input (termasuk plat baru)
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'plate_1' => 'nullable|string|max:20|unique:personal_qrs,license_plate', // Pastikan unik
        'plate_2' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
        'plate_3' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
        'plate_4' => 'nullable|string|max:20|unique:personal_qrs,license_plate',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Gunakan DB Transaction agar aman
    DB::beginTransaction();
    try {
        // 2. Buat user baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'member',
        ]);

        // 3. Buat 4 QR Pribadi (jika diisi)
        $plates = [
            'Pribadi 1' => $request->plate_1,
            'Pribadi 2' => $request->plate_2,
            'Pribadi 3' => $request->plate_3,
            'Pribadi 4' => $request->plate_4,
        ];

        foreach ($plates as $name => $plate) {
            if (!empty($plate)) {
                $customCode = now()->format('dmY') . strtoupper(Str::random(2));
                PersonalQr::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'license_plate' => $plate,
                    'code' => $customCode, // Kode QR unik
                    'status' => 'baru',
                ]);
            }
        }

        DB::commit(); // Sukses

        return redirect()->route('admin.members.index')
                         ->with('success', 'Member baru dan QR Pribadi berhasil ditambahkan.');
                         
    } catch (\Exception $e) {
        DB::rollBack(); // Gagal
        return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
    }
}

    /**
     * Menampilkan halaman detail spesifik member.
     * Rute: GET admin/members/{member}
     */
    public function show(User $member): View
    {
        // Pastikan kita hanya melihat detail 'member', bukan 'admin'
        if ($member->role === 'admin') {
            abort(404); 
        }

        // Eager load relasi: Ambil member INI BERSAMA SEMUA truk & tagihannya.
        // Ini efisien untuk menghindari N+1 query problem.
        $member->load('trucks', 'billings');

        // Arahkan ke view baru 'show.blade.php'
        return view('admin.members.show', compact('member'));
    }

    /**
     * Menampilkan form untuk mengedit member.
     */
    public function edit(User $member): View
    {
        if ($member->role === 'admin') {
            abort(404);
        }
        return view('admin.members.edit', compact('member'));
    }

    /**
     * Mengupdate data member di database.
     */
    public function update(Request $request, User $member): RedirectResponse
    {
        // Validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($member->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'ipl_status' => 'required|in:paid,unpaid', // <-- TAMBAHKAN VALIDASI INI
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        // Update data dasar
        $member->name = $data['name'];
        $member->email = $data['email'];
        $member->ipl_status = $data['ipl_status']; // <-- TAMBAHKAN PENYIMPANAN INI

        if (!empty($data['password'])) {
            $member->password = Hash::make($data['password']);
        }

        $member->save();

        return redirect()->route('admin.members.index')
                         ->with('success', 'Data member berhasil diperbarui.');
    }

    /**
     * Menghapus member dari database.
     */
    public function destroy(User $member): RedirectResponse
    {
        try {
            if ($member->role === 'admin' || $member->id === auth()->id()) {
                 return back()->with('error', 'Anda tidak bisa menghapus akun ini.');
            }
            
            // Relasi 'onDelete('cascade')' di migrasi 'trucks' 
            // akan otomatis menghapus semua data terkait.
            $member->delete();

            return redirect()->route('admin.members.index')
                             ->with('success', 'Member berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus member. Error: ' . $e->getMessage());
        }
    }
}