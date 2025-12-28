@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen QR Code</h1>
    <a href="{{ route('member.qrcodes.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Minta QR Code Baru
    </a>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
@endif

{{-- 1. Tambahkan ID pada card wrapper dan sembunyikan jika $pendingQrs kosong saat load awal --}}
<div class="card shadow mb-4 border-left-warning" id="pending-qr-card" @if($pendingQrs->isEmpty()) style="display: none;" @endif>
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Status Proses (Menunggu Konfirmasi Admin)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Plat Truk</th>
                        <th>Kode Unik</th>
                        <th>Status</th>
                        <th>Permintaan</th>
                    </tr>
                </thead>
                <tbody id="pending-qr-body">
                    {{-- Konten pertama kali dimuat oleh Blade --}}
                    @forelse ($pendingQrs as $qr)
                        <tr>
                            <td>{{ $qr->truck->license_plate ?? 'N/A' }}</td>
                            {{-- 2. Ganti Kode Unik dengan Teks --}}
                            <td><i>Masih menunggu Persetujuan admin</i></td>
                            <td><span class="badge badge-warning">PROSES</span></td>
                            <td>{{ $qr->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada permintaan QR Code yang sedang diproses.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow mb-4 mt-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">QR Code Siap Digunakan (Approved)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Untuk Truk (Plat)</th>
                        <!-- <th>Kode Unik</th> -->
                        <th>Status</th>
                        <th>Tgl. Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="approved-qr-body">
                    {{-- Konten pertama kali dimuat oleh Blade --}}
                    @forelse ($qrCodes as $qr)
                        <tr>
                            <td>{{ $qr->id }}</td>
                            <td>{{ $qr->truck->license_plate ?? 'N/A' }}</td>
                            <!-- <td><code>{{ $qr->code }}</code></td> -->
                            <td>
                                @if ($qr->status == 'baru')
                                    <span class="badge badge-primary">Approved</span>
                                @elseif ($qr->status == 'aktif')
                                    <span class="badge badge-success">Aktif (Di Dalam)</span>
                                @else
                                    <span class="badge badge-secondary">Selesai</span>
                                @endif
                            </td>
                            <td>{{ $qr->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('member.qrcodes.show', $qr->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-eye"></i> Lihat QR
                                </a>
                                
                                <form action="{{ route('member.qrcodes.destroy', $qr->id) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus QR Code ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            {{ $qr->status == 'aktif' ? 'disabled' : '' }}>
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada QR Code yang disetujui.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div id="pagination-links" class="d-flex justify-content-center">
            {{ $qrCodes->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- 3. Script AJAX Polling (Sudah Diupdate) --}}
<script>
// Fungsi helper untuk format waktu
function formatTime(dateTime) {
    if (!dateTime) return '-';
    return new Date(dateTime).toLocaleString('id-ID', { year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

// Fungsi helper untuk Status Approved
function getApprovedStatusBadge(status) {
    if (status == 'baru') {
        return '<span class="badge badge-primary">Approved</span>';
    } else if (status == 'aktif') {
        return '<span class="badge badge-success">Aktif (Di Dalam)</span>';
    } else {
        return '<span class="badge badge-secondary">Selesai</span>';
    }
}

function fetchQrData() {
    $.ajax({
        url: "{{ route('member.qrcodes.index') }}", // Panggil rute index
        type: 'GET',
        dataType: 'json', 
        headers: {
            'X-Requested-With': 'XMLHttpRequest' // Tandai sebagai AJAX
        },
        success: function(response) {
            var pendingBody = $('#pending-qr-body');
            var approvedBody = $('#approved-qr-body');
            pendingBody.empty();
            approvedBody.empty();
            
            // Sembunyikan paginasi (karena polling mengambil 'get()', bukan 'paginate()')
            $('#pagination-links').hide();

            // --- Isi Tabel Pending (SUDAH DIUPDATE) ---
            if (response.pendingQrs.length === 0) {
                // 3. Sembunyikan card jika tidak ada data
                $('#pending-qr-card').hide(); 
                pendingBody.append('<tr><td colspan="4" class="text-center">Tidak ada permintaan QR Code yang sedang diproses.</td></tr>');
            } else {
                // 3. Tampilkan card jika ada data
                $('#pending-qr-card').show(); 
                $.each(response.pendingQrs, function(index, qr) {
                    var row = '<tr>' +
                        '<td>' + (qr.truck ? qr.truck.license_plate : 'N/A') + '</td>' +
                        // 4. Ganti Kode Unik dengan Teks
                        '<td><i>Masih menunggu Persetujuan admin</i></td>' + 
                        '<td><span class="badge badge-warning">PROSES</span></td>' +
                        '<td>' + formatTime(qr.created_at) + '</td>' +
                        '</tr>';
                    pendingBody.append(row);
                });
            }

            // --- Isi Tabel Approved (SUDAH DIUPDATE) ---
            if (response.approvedQrs.length === 0) {
                approvedBody.append('<tr><td colspan="6" class="text-center">Belum ada QR Code yang disetujui.</td></tr>');
            } else {
                $.each(response.approvedQrs, function(index, qr) {
                    var showUrl = '{{ url("member/qrcodes") }}/' + qr.id;
                    var deleteUrl = '{{ url("member/qrcodes") }}/' + qr.id;
                    var csrfToken = "{{ csrf_token() }}";
                    var deleteDisabled = qr.status === 'aktif' ? 'disabled' : '';

                    var actionBtns = 
                        '<a href="' + showUrl + '" class="btn btn-sm btn-success"><i class="fas fa-eye"></i> Lihat QR</a> ' +
                        '<form action="' + deleteUrl + '" method="POST" class="d-inline" onsubmit="return confirm(\'Anda yakin ingin menghapus QR Code ini?\');">' +
                        '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                        '<input type="hidden" name="_method" value="DELETE">' +
                        '<button type="submit" class="btn btn-sm btn-danger" ' + deleteDisabled + '><i class="fas fa-trash"></i> Hapus</button>' +
                        '</form>';

                    var row = '<tr>' +
                        '<td>' + qr.id + '</td>' +
                        '<td>' + (qr.truck ? qr.truck.license_plate : 'N/A') + '</td>' +
                        // '<td><code>' + qr.code + '</code></td>' +
                        '<td>' + getApprovedStatusBadge(qr.status) + '</td>' +
                        '<td>' + formatTime(qr.created_at) + '</td>' +
                        '<td>' + actionBtns + '</td>' +
                        '</tr>';
                    approvedBody.append(row);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Gagal memuat data QR:", error);
        }
    });
}

$(document).ready(function() {
    // Polling setiap 5 detik
    setInterval(fetchQrData, 5000); 
});
</script>
@endpush