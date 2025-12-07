@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">QR Code Pribadi Saya</h1>
    <button class="btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#requestQrModal">
        <i class="fas fa-plus fa-sm text-white-50"></i> Request QR Baru
    </button>
</div>

{{-- MODAL REQUEST --}}
<div class="modal fade" id="requestQrModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('member.personal_qrs.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request QR Pribadi Baru</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Label / Nama Kendaraan</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Mobil Operasional 2" required>
                    </div>
                    <div class="form-group">
                        <label>Plat Nomor</label>
                        <input type="text" name="license_plate" class="form-control" placeholder="B 1234 XYZ" required>
                        <small class="text-muted">Plat nomor akan diformat otomatis (tanpa spasi).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    // Ambil request punya user ini yang belum diapprove (FILTER USER ID)
    $pendingRequests = \App\Models\PersonalQr::where('user_id', auth()->id())
                        ->where('is_approved', false)
                        ->get();
@endphp

@if($pendingRequests->count() > 0)
<div class="card shadow mb-4 border-left-warning">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Permintaan QR Saya (Menunggu Approval)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Plat Nomor</th>
                        <th>Tgl Request</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingRequests as $req)
                    <tr>
                        <td>{{ $req->name }}</td>
                        <td><strong>{{ $req->license_plate }}</strong></td>
                        <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge badge-warning">Menunggu Admin</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<div class="alert alert-info shadow-sm">
    <h6 class="font-weight-bold">Info</h6>
    <p class="mb-0">Ini adalah QR Code pribadi (reusable) yang terikat ke akun Anda. QR Code ini dapat digunakan berulang kali untuk plat nomor yang terdaftar.</p>
</div>

<div class="row" id="personal-qr-container">
    {{-- Konten QR yang SUDAH DIAPPROVE --}}
    {{-- Pastikan di Controller index() kamu memfilter where('is_approved', true) --}}
    @include('member.personal_qrs.partials.qr_list', ['personalQrs' => $personalQrs])
</div>

@endsection

@push('scripts')
{{-- Load SweetAlert (Jika belum ada di layout) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Variabel memori untuk menyimpan status terakhir
    var lastRequestsState = {};

    function checkMyQrStatus() {
        $.ajax({
            // Pastikan rute ini sudah dibuat di routes/web.php
            url: "{{ route('member.api.check.my.requests') }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                var currentRequests = response.requests;
                var shouldReload = false;

                // Loop cek data terbaru
                currentRequests.forEach(function(req) {
                    var oldState = lastRequestsState[req.id];

                    // Jika ini data baru (belum ada di memori), simpan saja
                    if (!oldState) {
                        lastRequestsState[req.id] = req;
                        return;
                    }

                    // 1. Cek: Dulu Belum Approve (0) -> Sekarang Sudah Approve (1)
                    if (oldState.is_approved == 0 && req.is_approved == 1) {
                        Swal.fire({
                            title: 'Disetujui!',
                            text: 'Permintaan QR untuk ' + req.plat + ' telah disetujui Admin!',
                            icon: 'success',
                            confirmButtonText: 'Lihat QR Baru'
                        }).then(() => {
                            location.reload(); // Reload agar tabel pindah ke bawah
                        });
                        shouldReload = true;
                    }

                    // Update memori
                    lastRequestsState[req.id] = req;
                });

                // 2. Cek Penghapusan (Ditolak)
                // Jika jumlah request di database (current) LEBIH SEDIKIT dari memori (last),
                // berarti ada yang dihapus/ditolak oleh admin.
                if (Object.keys(lastRequestsState).length > currentRequests.length) {
                     Swal.fire({
                        title: 'Permintaan Ditolak',
                        text: 'Salah satu permintaan QR Anda telah ditolak atau dihapus oleh Admin.',
                        icon: 'error',
                        confirmButtonText: 'Tutup'
                    }).then(() => {
                        location.reload();
                    });
                    
                    // Reset memori agar tidak loop alert
                    lastRequestsState = {}; 
                    currentRequests.forEach(r => lastRequestsState[r.id] = r);
                }
            },
            error: function(err) {
                // console.error("Gagal cek status:", err);
            }
        });
    }

    $(document).ready(function() {
        // Init memori awal saat load
        $.get("{{ route('member.api.check.my.requests') }}", function(data) {
            if(data.requests) {
                data.requests.forEach(r => lastRequestsState[r.id] = r);
            }
            
            // Mulai polling setelah init
            setInterval(checkMyQrStatus, 5000);
        });
    });
</script>
@endpush