<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billing; // Import model Billing
use App\Models\User;    // Import model User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BillingController extends Controller
{
    /**
     * Menampilkan daftar semua tagihan dari semua member.
     * Rute: GET admin/billings
     */
    public function index(): View
    {
        // Ambil semua tagihan, 'with('user')' untuk eager loading
        // agar kita bisa tampilkan nama member tanpa N+1 query
        $billings = Billing::with('user')->latest()->paginate(15);
        
        return view('admin.billings.index', compact('billings'));
    }

    /**
     * Menampilkan form untuk membuat tagihan manual.
     * Rute: GET admin/billings/create
     */
    public function create(): View
    {
        // Ambil semua user yang rolenya 'member' untuk dropdown
        $members = User::where('role', 'member')->orderBy('name')->get();
        return view('admin.billings.create', compact('members'));
    }

    /**
     * Menyimpan tagihan manual baru ke database.
     * Rute: POST admin/billings
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        Billing::create($validator->validated());

        return redirect()->route('admin.billings.index')
                         ->with('success', 'Tagihan baru berhasil dibuat.');
    }

    /**
     * Menampilkan detail satu tagihan.
     * Rute: GET admin/billings/{billing}
     */
    public function show(Billing $billing): View
    {
        // Load relasi user-nya
        $billing->load('user');
        return view('admin.billings.show', compact('billing'));
    }

    /**
     * Menampilkan form untuk mengedit tagihan (misal: ganti status).
     * Rute: GET admin/billings/{billing}/edit
     */
    public function edit(Billing $billing): View
    {
        // Ambil semua member untuk dropdown
        $members = User::where('role', 'member')->orderBy('name')->get();
        return view('admin.billings.edit', compact('billing', 'members'));
    }

    /**
     * Mengupdate data tagihan di database.
     * Rute: PUT/PATCH admin/billings/{billing}
     */
    public function update(Request $request, Billing $billing): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid',
            'due_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $billing->update($validator->validated());

        return redirect()->route('admin.billings.index')
                         ->with('success', 'Tagihan berhasil diperbarui.');
    }

    /**
     * Menghapus tagihan dari database.
     * Rute: DELETE admin/billings/{billing}
     */
    public function destroy(Billing $billing): RedirectResponse
    {
        try {
            $billing->delete();
            return redirect()->route('admin.billings.index')
                             ->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus tagihan. Error: ' . $e->getMessage());
        }
    }
}