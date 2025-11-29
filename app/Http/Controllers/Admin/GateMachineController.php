<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GateMachine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GateMachineController extends Controller
{
    /**
     * Menampilkan daftar mesin.
     */
    public function index(): View
    {
        $machines = GateMachine::latest()->paginate(10);
        return view('admin.gate_machines.index', compact('machines'));
    }

    /**
     * Form tambah mesin.
     */
    public function create(): View
    {
        return view('admin.gate_machines.create');
    }

    /**
     * Simpan mesin baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'termno' => 'required|string|unique:gate_machines,termno|max:10',
            'location' => 'required|string|max:255',
        ]);

        GateMachine::create($request->all());

        return redirect()->route('admin.gate-machines.index')
                         ->with('success', 'Mesin Gerbang berhasil ditambahkan.');
    }

    /**
     * Form edit mesin.
     */
    public function edit(GateMachine $gateMachine): View
    {
        return view('admin.gate_machines.edit', compact('gateMachine'));
    }

    /**
     * Update mesin.
     */
    public function update(Request $request, GateMachine $gateMachine): RedirectResponse
    {
        $request->validate([
            'termno' => [
                'required', 
                'string', 
                'max:10', 
                Rule::unique('gate_machines')->ignore($gateMachine->id)
            ],
            'location' => 'required|string|max:255',
        ]);

        $gateMachine->update($request->all());

        return redirect()->route('admin.gate-machines.index')
                         ->with('success', 'Data Mesin Gerbang berhasil diperbarui.');
    }

    /**
     * Hapus mesin.
     */
    public function destroy(GateMachine $gateMachine): RedirectResponse
    {
        $gateMachine->delete();

        return redirect()->route('admin.gate-machines.index')
                         ->with('success', 'Mesin Gerbang berhasil dihapus.');
    }
}