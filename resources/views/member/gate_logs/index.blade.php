@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Histori Truk Saya</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Log Aktivitas Truk Anda</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Plat Nomor</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Tagihan (Rp)</th>
                    </tr>
                </thead>
                {{-- 1. Wadah (tbody) untuk AJAX Polling --}}
                <tbody id="log-table-body">
                    {{-- 2. Konten pertama kali dimuat oleh Blade --}}
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->truck->license_plate ?? 'N/A' }}</td>
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
                            <td>{{ $log->billing_amount ? number_format($log->billing_amount, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada histori aktivitas truk Anda.</td>
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
{{-- 4. Script AJAX Polling --}}
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

function fetchMemberLogData(url = "{{ route('member.gate.logs') }}") {
    $.ajax({
        url: url, // Gunakan URL dari paginasi jika ada
        type: 'GET',
        dataType: 'json', 
        headers: {
            'X-Requested-With': 'XMLHttpRequest' 
        },
        success: function(response) {
            var tableBody = $('#log-table-body');
            tableBody.empty(); 

            if (!response.data || response.data.length === 0) {
                tableBody.append('<tr><td colspan="5" class="text-center">Belum ada histori aktivitas truk Anda.</td></tr>');
                $('#pagination-links').empty();
                return;
            }

            $.each(response.data, function(index, log) {
                
                var plateNumber = (log.truck ? log.truck.license_plate : 'N/A');
                
                var row = '<tr>' +
                    '<td>' + plateNumber + '</td>' +
                    '<td>' + formatTime(log.check_in_at) + '</td>' +
                    '<td>' + formatTime(log.check_out_at) + '</td>' +
                    '<td>' + getStatusBadge(log.status) + '</td>' +
                    '<td>' + formatRupiah(log.billing_amount) + '</td>' +
                    '</tr>';
                tableBody.append(row);
            });
            
            $('#pagination-links').html(response.pagination);
        },
        error: function(xhr, status, error) {
            console.error("Gagal memuat data log member:", error);
        }
    });
}

$(document).ready(function() {
    
    // Polling setiap 5 detik
    setInterval(function() {
        // Ambil URL halaman paginasi yang sedang aktif, atau default ke halaman 1
        var currentPageUrl = $('#pagination-links .active a').attr('href') || "{{ route('member.gate.logs') }}";
        fetchMemberLogData(currentPageUrl);
    }, 5000); 
    
    // Handle Paginasi (Memuat halaman baru via AJAX)
    $(document).on('click', '#pagination-links .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        fetchMemberLogData(url);
    });
});
</script>
@endpush