<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalQr;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // <-- Import Rule
use Illuminate\Support\Str; // <-- Import Str

class PersonalQrController extends Controller
{
    /**
     * Menampilkan daftar semua QR Code Pribadi (Reusable).
     * Merespons HTML atau JSON (untuk AJAX).
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = PersonalQr::with('user')->latest();

        if ($request->ajax()) {
            $qrCodes = $query->take(20)->get();
            return response()->json(['data' => $qrCodes]);
        }

        $personalQrs = $query->paginate(20);
        return view('admin.personal_qrs.index', compact('personalQrs'));
    }

    /**
     * Menampilkan form edit QR Pribadi (misal: ganti plat).
     * Rute: GET /admin/personal-qrs/{personalQr}/edit
     */
    public function edit(PersonalQr $personalQr): View
    {
        return view('admin.personal_qrs.edit', compact('personalQr'));
    }

    /**
     * Mengupdate QR Pribadi.
     * Rute: PUT /admin/personal-qrs/{personalQr}
     */
    public function update(Request $request, PersonalQr $personalQr): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'license_plate' => [
                'required',
                'string',
                'max:20',
                // Pastikan unik, KECUALI untuk ID QR ini sendiri
                Rule::unique('personal_qrs')->ignore($personalQr->id),
            ],
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update data
        $personalQr->license_plate = $request->license_plate;
        $personalQr->name = $request->name;

        // Cek jika Admin minta regenerasi kode QR
        if ($request->has('regenerate_code')) {
           $personalQr->code = now()->format('dmY')  . strtoupper(Str::random(8));
        }

        $personalQr->save();

        return redirect()->route('admin.personal-qrs.index')
                     ->with('success', 'QR Pribadi berhasil diperbarui.');
    }

    // (Method create, store, destroy bisa ditambahkan di sini nanti)
}