@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Persetujuan QR Code</h1>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div class="alert alert-danger shadow-sm">
    {{ session('error') }}
</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Permintaan QR Code Baru (Menunggu Persetujuan)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Plat Truk</th>
                        <th>Kode</th>
                        <th>Tgl. Permintaan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                {{-- 1. Wadah (tbody) untuk AJAX Polling --}}
                <tbody id="approval-table-body">
                    
                    {{-- 2. Konten pertama kali dimuat oleh Blade (sesuai permintaanmu) --}}
                    @forelse ($qrCodes as $qr)
                        <tr>
                            <td>{{ $qr->id }}</td>
                            <td>{{ $qr->truck->user->name ?? 'N/A' }}</td> 
                            <td><strong>{{ $qr->truck->license_plate ?? 'N/A' }}</strong></td>
                            <td><code>{{ $qr->code }}</code></td>
                            <td>{{ $qr->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('admin.qr.approvals.approve', $qr->id) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menyetujui QR Code ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada permintaan QR Code baru.</td>
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
{{-- 3. Script AJAX Polling --}}
<script>
// Fungsi helper untuk format waktu
function formatTime(dateTime) {
    if (!dateTime) return '-';
    return new Date(dateTime).toLocaleString('id-ID', { year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function fetchApprovalData() {
    $.ajax({
        url: "{{ route('admin.qr.approvals.index') }}",
        type: 'GET',
        dataType: 'json', 
        headers: {
            'X-Requested-With': 'XMLHttpRequest' 
        },
        success: function(response) {
            var tableBody = $('#approval-table-body');
            tableBody.empty(); // Kosongkan tabel lama

            if (response.data.length === 0) {
                tableBody.append('<tr><td colspan="6" class="text-center">Tidak ada permintaan QR Code baru.</td></tr>');
                $('#pagination-links').hide(); // Sembunyikan paginasi saat polling
                return;
            }

            // Loop melalui data JSON dan buat baris HTML
            $.each(response.data, function(index, qr) {
                
                // Buat Form Action
                // Kita harus hati-hati membangun form action dengan @csrf
                // Cara paling aman adalah tetap menggunakan reload Halaman Penuh untuk Aksi
                // Di bawah ini kita buat ulang form-nya.
                
                var actionUrl = "{{ url('admin/qr-approvals') }}/" + qr.id + "/approve";
                var csrfToken = "{{ csrf_token() }}";

                var actionForm = 
                    '<form action="' + actionUrl + '" method="POST" onsubmit="return confirm(\'Anda yakin ingin menyetujui QR Code ini?\');">' +
                    '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                    '<button type="submit" class="btn btn-sm btn-success">' +
                    '<i class="fas fa-check"></i> Setujui' +
                    '</button>' +
                    '</form>';

                var row = '<tr>' +
                    '<td>' + qr.id + '</td>' +
                    '<td>' + (qr.truck.user ? qr.truck.user.name : 'N/A') + '</td>' +
                    '<td><strong>' + (qr.truck ? qr.truck.license_plate : 'N/A') + '</strong></td>' +
                    '<td><code>' + qr.code + '</code></td>' +
                    '<td>' + formatTime(qr.created_at) + '</td>' +
                    '<td>' + actionForm + '</td>' +
                    '</tr>';
                tableBody.append(row);
            });
            
            // Sembunyikan paginasi saat polling
            $('#pagination-links').hide(); 
        },
        error: function(xhr, status, error) {
            console.error("Gagal memuat data persetujuan:", error);
        }
    });
}

$(document).ready(function() {
    // Polling setiap 5 detik
    setInterval(fetchApprovalData, 5000); 
});
</script>
@endpush