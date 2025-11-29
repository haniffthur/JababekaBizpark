<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Menampilkan halaman pengaturan.
     */
    public function index(): View
    {
        // Ambil nilai tarif saat ini
        $overnightRate = Setting::getValue('overnight_rate', 10000);
        
        // --- TAMBAHKAN INI ---
        // Ambil mode billing saat ini. Default-nya 'local'.
        $billingMode = Setting::getValue('billing_integration_mode', 'local');

        return view('admin.settings.index', compact('overnightRate', 'billingMode'));
    }

    /**
     * Mengupdate pengaturan.
     */
    public function update(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'overnight_rate' => 'required|numeric|min:0',
            'billing_integration_mode' => 'required|in:local,ipl', // <-- Validasi baru
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Simpan nilai tarif
        Setting::setValue('overnight_rate', $request->overnight_rate);
        
        // --- TAMBAHKAN INI ---
        // Simpan nilai mode billing
        Setting::setValue('billing_integration_mode', $request->billing_integration_mode);

        return redirect()->route('admin.settings.index')
                         ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}