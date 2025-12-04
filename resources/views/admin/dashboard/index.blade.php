@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
</div>

<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Member</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-members">{{ $emptyStats['total_members'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Truk di Dalam Gudang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="trucks-inside">{{ $emptyStats['trucks_inside'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Tagihan Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-billings">Rp {{ number_format($emptyStats['pending_billings'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Aktivitas Gate (Hari Ini)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="logs-today">{{ $emptyStats['logs_today'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Gerbang (7 Hari Terakhir)</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="adminActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Log Aktivitas Terbaru</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="recent-logs-table">
                        <thead>
                            <tr>
                                <th class="pl-4">Status</th>
                                <th>Plat & Aksi</th>
                                <th class="text-right pr-4">Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="recent-logs-body">
                            {{-- Konten Awal Dimuat Blade --}}
                            @forelse ($recentLogs as $log)
                                <tr>
                                    <td class="pl-4">
                                        <i class="fas fa-circle {{ $log->status == 'Berhasil Masuk' ? 'text-success' : ($log->status == 'Berhasil Keluar' ? 'text-info' : 'text-danger') }}"></i>
                                    </td>
                                    <td>
    {{-- LOGIKA IF/ELSE: Cek Truck ID atau License Plate langsung --}}
    @if($log->truck_id)
        <strong>{{ $log->truck->license_plate ?? 'N/A' }}</strong>
    @else
        <strong>{{ $log->license_plate ?? 'N/A' }}</strong>
    @endif
    <br>
    <small>{{ $log->status }}</small>
</td>
<td class="text-right pr-4">
    {{ $log->created_at->diffForHumans() }}<br>
    
    {{-- LOGIKA USER --}}
    <small>
        @if($log->truck_id)
            {{ $log->truck->user->name ?? 'Guest' }}
        @else
            {{ $log->user->name ?? 'Guest' }}
        @endif
    </small>
</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center p-4">Memuat data...</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('admin.gate.logs') }}" class="small text-primary">Lihat Semua Log</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script>

    
// Helper functions (Rupiah, TimeAgo)
function formatRupiah(angka) {
    var num = parseFloat(angka);
    if (isNaN(num)) return 'Rp 0';
    return 'Rp ' + num.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
function formatTimeAgo(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    if (seconds < 60) return seconds + ' detik lalu';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + ' menit lalu';
    const hours = Math.floor(minutes / 24) * 24 / 24; // Simple version
    if (hours < 24) return Math.floor(minutes / 60) + ' jam lalu';
    return Math.floor(hours / 24) + ' hari lalu';
}
function updateAdminDashboard() {
    $.ajax({
        url: "{{ route('admin.data.stats') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Update Info Cards
            $('#total-members').text(response.stats.total_members);
            $('#trucks-inside').text(response.stats.trucks_inside);
            $('#logs-today').text(response.stats.logs_today);
            $('#pending-billings').text(formatRupiah(response.stats.pending_billings));

            // Update Recent Logs Table
            var logBody = $('#recent-logs-body');
            logBody.empty();
            
            if (response.recentLogs.length === 0) {
                logBody.append('<tr><td colspan="3" class="text-center p-4">Tidak ada aktivitas.</td></tr>');
                return;
            }

            $.each(response.recentLogs, function(index, log) {
                var statusClass = log.status.includes('Berhasil Masuk') ? 'text-success' : (log.status.includes('Berhasil Keluar') ? 'text-info' : 'text-danger');
                var timeAgo = formatTimeAgo(log.created_at);

                // --- LOGIKA BARU: Tentukan Plat & Member ---
                var plateNumber = 'N/A';
                var memberName = 'Guest';

                if (log.truck_id && log.truck) {
                    // Log Truk
                    plateNumber = log.truck.license_plate;
                    memberName = (log.truck.user ? log.truck.user.name : 'Guest');
                } else {
                    // Log Pribadi (Cek license_plate langsung)
                    plateNumber = log.license_plate || 'N/A';
                    memberName = (log.user ? log.user.name : 'Guest');
                }
                // --------------------------------------------

                var row = '<tr>' +
                    '<td class="pl-4"><i class="fas fa-circle ' + statusClass + '"></i></td>' +
                    '<td><strong>' + plateNumber + '</strong><br><small>' + log.status + '</small></td>' +
                    '<td class="text-right pr-4">' + timeAgo + '<br><small>' + memberName + '</small></td>' +
                    '</tr>';
                logBody.append(row);
            });
        }
    });
}
// CHART SCRIPT (Hanya perlu diinisialisasi sekali)
// ... (Tulis ulang semua script Chart.js Admin di sini) ...
// (Saya tidak menulis ulang kode Chart.js di sini agar jawaban tidak terlalu panjang, tapi pastikan kode di langkah sebelumnya ada di sini)
// ...
var ctxAdmin = document.getElementById("adminActivityChart");
// ... (Definisi Chart kamu) ...

$(document).ready(function() {
    // Jalankan pertama kali
    updateAdminDashboard();
    
    // Polling setiap 5 detik
    setInterval(updateAdminDashboard, 5000); 
});
</script>
@endpush