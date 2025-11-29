@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan & Log Sistem</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
    </div>
    <div class="card-body">
        {{-- 1. Beri ID pada Form --}}
        <form id="filter-form" method="GET" action="{{ route('admin.gate.logs') }}">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="license_plate">Plat Nomor</label>
                        <input type="text" class="form-control" id="license_plate" name="license_plate" 
                               value="{{ $filters['license_plate'] ?? '' }}" 
                               placeholder="Cari plat nomor...">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <input type="text" class="form-control" id="status" name="status" 
                               value="{{ $filters['status'] ?? '' }}" 
                               placeholder="Cari status (cth: Berhasil Masuk, Gagal, dll)">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group" style="width: 100%;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Semua Log Aktivitas Gerbang</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Plat Nomor</th>
                        <th>Member</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Tagihan (Rp)</th>
                    </tr>
                </thead>
                {{-- 2. Wadah (tbody) untuk AJAX Polling --}}
                <tbody id="log-table-body">
                    {{-- 3. Konten pertama kali dimuat oleh Blade --}}
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            
                            @if ($log->truck_id)
                                {{-- Jika ini Log TRUK --}}
                                <td><strong>{{ $log->truck->license_plate ?? 'N/A' }}</strong></td>
                                <td>{{ $log->truck->user->name ?? 'N/A' }}</td>
                            @else
                                {{-- Jika ini Log PRIBADI --}}
                                <td><strong>{{ $log->license_plate ?? 'N/A' }}</strong></td>
                                <td>{{ $log->user->name ?? 'N/A' }}</td>
                            @endif
                            
                            <td>{{ $log->check_in_at ? $log->check_in_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>{{ $log->check_out_at ? $log->check_out_at->format('d/m/Y H:i') : '-' }}</td>
                            <td>
                                @if (Str::contains($log->status, 'Berhasil'))
                                    <span class="badge badge-success">{{ $log->status }}</span>
                                @elseif (Str::contains($log->status, 'Gagal'))
                                    <span class="badge badge-danger">{{ $log->status }}</span>
                                @else
                                    <span class="badge badge-info">{{ $log->status }}</span>
                                @endif
                            </td>
                            <td>{{ $log->notes ?? '-' }}</td>
                            <td>{{ $log->billing_amount ? number_format($log->billing_amount, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada log aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div id="pagination-links" class="d-flex justify-content-center">
            {{ $logs->links('vendor.pagination.bootstrap-4') }}
        </div>

    </div>
</div>

@endsection

@push('scripts')
{{-- 5. Script AJAX Polling (JSON) --}}
<script>
// Fungsi helper
function formatRupiah(angka) {
    var num = parseFloat(angka);
    if (isNaN(num) || num === null) return '-';
    var reverse = num.toFixed(0).toString().split('').reverse().join(''),
        ribuan = reverse.match(/\d{1,3}/g);
    ribuan = ribuan.join('.').split('').reverse().join('');
    return 'Rp ' + ribuan;
}
function formatTime(dateTime) {
    if (!dateTime) return '-';
    return new Date(dateTime).toLocaleString('id-ID', { year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
function getStatusBadge(status) {
    if (!status) return '';
    if (status.includes('Berhasil')) {
        return '<span class="badge badge-success">' + status + '</span>';
    } else if (status.includes('Gagal')) {
        return '<span class="badge badge-danger">' + status + '</span>';
    } else {
        return '<span class="badge badge-info">' + status + '</span>';
    }
}

// Fungsi utama Polling
function fetchLogData(url = "{{ route('admin.gate.logs.data') }}") { // <-- Panggil rute data
    var filterData = $('#filter-form').serialize();

    // Tambahkan filter ke URL (jika belum ada)
    var ajaxUrl = url;
    if (url.indexOf('?') > -1){
        ajaxUrl = url + '&' + filterData;
    } else {
        ajaxUrl = url + '?' + filterData;
    }

    $.ajax({
        url: ajaxUrl,
        type: 'GET',
        dataType: 'json', 
        headers: {
            'X-Requested-With': 'XMLHttpRequest' 
        },
        success: function(response) {
            var tableBody = $('#log-table-body');
            tableBody.empty(); 

            if (!response.data || response.data.length === 0) {
                tableBody.append('<tr><td colspan="9" class="text-center">Belum ada log aktivitas.</td></tr>');
                $('#pagination-links').empty();
                return;
            }

            // Render baris tabel dari JSON
            $.each(response.data, function(index, log) {
                var plateNumber = 'N/A';
                var memberName = 'N/A';
                
                if (log.truck_id && log.truck) {
                    plateNumber = '<strong>' + (log.truck.license_plate || 'N/A') + '</strong>';
                    memberName = (log.truck.user ? log.truck.user.name : 'N/A');
                } else if (log.user_id && log.user) {
                    plateNumber = '<strong>' + (log.license_plate || 'N/A') + '</strong>';
                    memberName = (log.user ? log.user.name : 'N/A');
                } else if (log.truck_id) {
                    plateNumber = '<strong>(Truk Dihapus)</strong>';
                }
                
                var row = '<tr>' +
                    '<td>' + log.id + '</td>' +
                    '<td>' + plateNumber + '</td>' +
                    '<td>' + memberName + '</td>' +
                    '<td>' + formatTime(log.check_in_at) + '</td>' +
                    '<td>' + formatTime(log.check_out_at) + '</td>' +
                    '<td>' + getStatusBadge(log.status) + '</td>' +
                    '<td>' + (log.notes ? log.notes : '-') + '</td>' +
                    '<td>' + formatRupiah(log.billing_amount) + '</td>' +
                    '</tr>';
                tableBody.append(row);
            });
            
            // Render Paginasi
            $('#pagination-links').html(response.pagination);
        },
        error: function(xhr, status, error) {
            console.error("Gagal memuat data log:", error);
        }
    });
}

$(document).ready(function() {
    
    // Polling setiap 5 detik
    setInterval(function() {
        // Ambil URL halaman paginasi yang sedang aktif
        var currentPageUrl = $('#pagination-links .page-item.active .page-link').attr('href');
        // Jika tidak ada halaman aktif (halaman 1), gunakan rute data default
        var urlToFetch = currentPageUrl || "{{ route('admin.gate.logs.data') }}"; 
        
        fetchLogData(urlToFetch);
    }, 5000); 

    // Handle Filter (jika user klik filter)
    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        // Saat filter, selalu kembali ke halaman 1 (rute data default)
        fetchLogData("{{ route('admin.gate.logs.data') }}"); 
    });
    
    // Handle Paginasi (Memuat halaman baru via AJAX)
    $(document).on('click', '#pagination-links .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href'); // Ambil URL dari tombol paginasi
        fetchLogData(url);
    });
});
</script>
@endpush