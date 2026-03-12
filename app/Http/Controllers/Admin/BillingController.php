<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\User;
use App\Models\Setting;
use App\Models\DailyCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class BillingController extends Controller
{
    // Menampilkan halaman Index
 public function index(Request $request)
{
    $billings = Billing::with('user')->latest()->paginate(10);

    if ($request->ajax()) {
        $html = view('admin.billings.partials.table_body', compact('billings'))->render();
        $pagination = $billings->links()->toHtml();
        return response()->json(['html' => $html, 'pagination' => $pagination]);
    }

    return view('admin.billings.index', compact('billings'));
}

    // GENERATE BILLS (Manual Trigger)
    public function generateBills()
    {
        DB::beginTransaction();
        try {
            // 1. Ambil semua member aktif
            $members = User::where('role', 'member')->get();
            $billsGenerated = 0;

            foreach ($members as $member) {
                
                // 2. Cari data inap truk (DailyCharges) milik member ini yang BELUM ditagihkan
                // Asumsi: menggunakan relasi melalui truk dan kolom 'ipl_bill_id' masih null
                $unbilledCharges = \App\Models\DailyCharge::whereHas('truck', function($q) use ($member) {
                    $q->where('user_id', $member->id);
                })->whereNull('ipl_bill_id')->get();

                // Hitung total biaya inap truk
                $totalInap = $unbilledCharges->sum('amount');

                // ----------------------------------------------------
                // BIAYA IPL SEMENTARA DIMATIKAN
                // $biayaIPL = 150000; // Contoh nominal IPL
                // $totalTagihan = $totalInap + $biayaIPL;
                // ----------------------------------------------------
                
                // HANYA GUNAKAN TOTAL INAP
                $totalTagihan = $totalInap;

                // 3. JIKA ADA TAGIHAN (> 0), MAKA BUAT BILLING BARU
                // Jika totalnya 0 (karena tidak ada truk inap & IPL dimatikan), lewati member ini.
                if ($totalTagihan > 0) {
                    
                    // Buat Tagihan Induk
                    $billing = Billing::create([
                        'user_id' => $member->id,
                        'total_amount' => $totalTagihan,
                        'description' => 'Tagihan Biaya Inap Truk',
                        'status' => 'unpaid',
                        'due_date' => now()->addDays(7), // Jatuh tempo 7 hari
                    ]);

                    // Masukkan ID Tagihan (ipl_bill_id) ke data inap agar statusnya menjadi "Sudah Ditagih"
                    foreach($unbilledCharges as $charge) {
                        $charge->update([
                            'ipl_bill_id' => $billing->id
                        ]);
                    }
                    
                    $billsGenerated++;
                }
            }

            DB::commit();

            if ($billsGenerated > 0) {
                return response()->json(['message' => "$billsGenerated Tagihan Inap Truk berhasil dibuat."]);
            } else {
                return response()->json(['message' => "Tidak ada tagihan yang perlu dibuat (Semua truk sudah ditagih)."]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }
    }

    // APPROVE (Verifikasi Pembayaran)
    public function approve($id)
    {
        $billing = Billing::findOrFail($id);
        $billing->update(['status' => 'paid']);
        
        // Buka blokir user jika semua tagihan lunas (opsional logic: atau per tagihan)
        $billing->user->update(['ipl_status' => 'paid']);

        return back()->with('success', 'Pembayaran disetujui. Akses member dibuka.');
    }

    // REJECT (Tolak Pembayaran)
    public function reject($id)
    {
        $billing = Billing::findOrFail($id);
        $billing->update(['status' => 'rejected']);
        // Tidak mengubah status user (tetap unpaid)
        return back()->with('error', 'Pembayaran ditolak.');
    }

    // DESTROY (Hapus Tagihan)
    public function destroy($id)
    {
        $billing = Billing::findOrFail($id);

        // OPSI: Jika tagihan dihapus, kembalikan status DailyCharge agar bisa ditagih ulang?
        // Atau ikut terhapus? Di sini kita kembalikan (Reset) agar tidak rugi.
        DailyCharge::where('ipl_bill_id', $billing->id)->update([
            'is_billed' => false,
            'ipl_bill_id' => null
        ]);

        if ($billing->proof_image) {
            Storage::disk('public')->delete($billing->proof_image);
        }

        $billing->delete();
        return back()->with('success', 'Tagihan dihapus. Biaya inap dikembalikan ke antrean.');
    }

    public function show($id)
{
    // Ambil billing beserta user dan rincian daily charges (biaya inap)
    $billing = Billing::with(['user', 'dailyCharges.truck'])->findOrFail($id);

    // Format data biar enak dibaca di JS
    return response()->json([
        'id' => $billing->id,
        'user_name' => $billing->user->name ?? 'User Dihapus',
        'user_email' => $billing->user->email ?? '-',
        'status' => $billing->status,
        'created_at' => $billing->created_at->format('d M Y'),
        'due_date' => $billing->due_date ? Carbon::parse($billing->due_date)->format('d M Y') : '-',
        'total_formatted' => 'Rp ' . number_format($billing->total_amount, 0, ',', '.'),
        
        // Kirim rincian item (IPL + Daily Charges)
        'items' => $this->getBillingItems($billing) 
    ]);
}

// Helper private untuk menyusun list item
private function getBillingItems($billing)
{
    $items = [];

    // 1. Masukkan IPL (Asumsi IPL adalah sisa dari Total - Total Daily Charges)
    // Atau ambil dari Setting jika mau fix
    $totalDaily = $billing->dailyCharges->sum('amount');
    $iplAmount = $billing->total_amount - $totalDaily;

    if ($iplAmount > 0) {
        $items[] = [
            'desc' => 'Biaya IPL (Bulanan)',
            'date' => $billing->created_at->format('d M Y'),
            'amount' => 'Rp ' . number_format($iplAmount, 0, ',', '.')
        ];
    }

    // 2. Masukkan Rincian Menginap
    foreach($billing->dailyCharges as $charge) {
        $plat = $charge->truck->license_plate ?? 'Truk Dihapus';
        $items[] = [
            'desc' => "Biaya Inap Truk ($plat)",
            'date' => Carbon::parse($charge->charge_date)->format('d M Y'),
            'amount' => 'Rp ' . number_format($charge->amount, 0, ',', '.')
        ];
    }

    return $items;
}
}