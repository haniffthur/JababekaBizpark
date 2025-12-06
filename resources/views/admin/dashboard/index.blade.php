@extends('layouts.app')

@section('content')

{{-- 1. LOAD LIBARARY --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
{{-- Kita load CSS SweetAlert di sini untuk memastikan --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* (Style CSS Modern yang sama seperti sebelumnya) */
    .filter-bar { background: #fff; border-radius: 12px; padding: 1rem 1.5rem; box-shadow: 0 2px 15px rgba(0,0,0,0.03); border: 1px solid #f1f3f9; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    .filter-group { display: flex; align-items: center; background: #f8f9fc; border-radius: 8px; padding: 0.5rem 1rem; border: 1px solid #eaecf4; flex: 1; min-width: 200px; }
    .filter-group i { color: #b7b9cc; margin-right: 10px; }
    .filter-input { border: none; background: transparent; width: 100%; font-size: 0.9rem; color: #5a5c69; outline: none; }
    .filter-input:focus { outline: none; }
    .btn-filter { border-radius: 8px; padding: 0.6rem 1.5rem; font-weight: 600; box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2); transition: all 0.2s; }
    .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 6px 8px rgba(78, 115, 223, 0.3); }
    .card-clean { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .card-clean:hover { transform: translateY(-3px); }
    .icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
    .bg-gradient-primary-soft { background: linear-gradient(135deg, rgba(78,115,223,0.1) 0%, rgba(78,115,223,0.2) 100%); color: #4e73df; }
    .bg-gradient-success-soft { background: linear-gradient(135deg, rgba(28,200,138,0.1) 0%, rgba(28,200,138,0.2) 100%); color: #1cc88a; }
    .bg-gradient-info-soft    { background: linear-gradient(135deg, rgba(54,185,204,0.1) 0%, rgba(54,185,204,0.2) 100%); color: #36b9cc; }
    .bg-gradient-warning-soft { background: linear-gradient(135deg, rgba(246,194,62,0.1) 0%, rgba(246,194,62,0.2) 100%); color: #f6c23e; }
    .table-modern td { vertical-align: middle; }
    .badge-soft-success { background-color: #d1e7dd; color: #0f5132; }
    .badge-soft-info { background-color: #cff4fc; color: #055160; }
    .badge-soft-danger { background-color: #f8d7da; color: #842029; }
</style>
@endpush

{{-- HEADER --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Dashboard</h1>
        <p class="mb-0 text-muted small">Overview aktivitas Gudang Jababeka secara real-time.</p>
    </div>
    <div class="d-flex align-items-center">
        <span class="mr-3 small text-muted"><i class="fas fa-circle text-success mr-1" style="font-size: 8px;"></i> Live Update</span>
        <button class="btn btn-sm btn-light shadow-sm" onclick="updateDashboard()">
            <i class="fas fa-sync-alt fa-sm text-gray-400"></i>
        </button>
    </div>
</div>

{{-- FILTER BAR --}}
<form id="dashboard-filter-form" class="mb-4">
    <div class="filter-bar">
        <div class="filter-group">
            <i class="fas fa-calendar-alt"></i>
            <select name="filter" id="filter" class="filter-input">
                <option value="this_month">Bulan Ini</option>
                <option value="this_week">Minggu Ini</option>
                <option value="today">Hari Ini</option>
                <option value="custom">Pilih Tanggal...</option>
            </select>
        </div>
        <div class="filter-group" id="custom-date-range" style="display: none;">
            <i class="fas fa-clock"></i>
            <input type="text" id="flatpickr-range" class="filter-input" placeholder="Pilih Rentang">
            <input type="hidden" name="start_date" id="start_date">
            <input type="hidden" name="end_date" id="end_date">
        </div>
        <div class="filter-group">
            <i class="fas fa-car"></i>
            <input type="text" name="license_plate" id="license_plate" class="filter-input" placeholder="Cari Plat Nomor...">
        </div>
        <div class="filter-group">
            <i class="fas fa-info-circle"></i>
            <select name="status" id="status" class="filter-input">
                <option value="">Semua Status</option>
                <option value="Berhasil">✅ Berhasil</option>
                <option value="Gagal">❌ Gagal/Ditolak</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-filter"><i class="fas fa-filter mr-1"></i> Filter</button>
    </div>
</form>

{{-- INFO CARDS --}}
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Traffic</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="total-traffic">{{ $emptyStats['total_traffic'] ?? 0 }}</div>
                        <small class="text-muted">Hari Ini</small>
                    </div>
                    <div class="col-auto"><div class="icon-box bg-gradient-primary-soft"><i class="fas fa-exchange-alt"></i></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sedang Di Dalam</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="vehicles-inside">{{ $emptyStats['vehicles_inside'] ?? 0 }}</div>
                        <small class="text-muted">Truk & Pribadi</small>
                    </div>
                    <div class="col-auto"><div class="icon-box bg-gradient-success-soft"><i class="fas fa-warehouse"></i></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Est. Pendapatan</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="revenue-month">Rp {{ number_format($emptyStats['revenue_month'] ?? 0, 0, ',', '.') }}</div>
                        <small class="text-muted">Tagihan IPL</small>
                    </div>
                    <div class="col-auto"><div class="icon-box bg-gradient-info-soft"><i class="fas fa-dollar-sign"></i></div></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Member</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="total-members">{{ $emptyStats['total_members'] ?? 0 }}</div>
                        <small class="text-muted">Pengguna Terdaftar</small>
                    </div>
                    <div class="col-auto"><div class="icon-box bg-gradient-warning-soft"><i class="fas fa-users"></i></div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS & LOGS --}}
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card card-clean mb-4">
            <div class="card-header py-3 bg-white border-bottom-0 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Statistik <span id="chart-label-period">7 Hari Terakhir</span></h6>
            </div>
            <div class="card-body"><div class="chart-area" style="height: 320px;"><canvas id="adminActivityChart"></canvas></div></div>
        </div>
    </div>
    <div class="col-xl-4 col-lg-5">
        <div class="card card-clean mb-4">
            <div class="card-header py-3 bg-white border-bottom-0 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
                <a href="{{ route('admin.gate.logs') }}" class="text-xs font-weight-bold text-primary text-uppercase" style="text-decoration: none;">Lihat Semua &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                    <table class="table table-borderless table-hover mb-0">
                        <tbody id="recent-logs-body">
                            {{-- Data Awal --}}
                            @forelse ($recentLogs as $log)
                                @php
                                    $badgeColor = 'secondary'; $icon = 'fa-question';
                                    if(str_contains($log->status, 'Masuk')) { $badgeColor = 'success'; $icon = 'fa-arrow-down'; }
                                    elseif(str_contains($log->status, 'Keluar')) { $badgeColor = 'info'; $icon = 'fa-arrow-up'; }
                                    else { $badgeColor = 'danger'; $icon = 'fa-times'; }
                                    $plate = $log->truck_id ? ($log->truck->license_plate ?? 'N/A') : ($log->license_plate ?? 'N/A');
                                    $user = ($log->truck_id && $log->truck && $log->truck->user) ? $log->truck->user->name : (($log->user && $log->user->name) ? $log->user->name : 'Guest');
                                @endphp
                                <tr>
                                    <td class="pl-4 align-middle"><span class="badge badge-soft-{{ $badgeColor }} p-2 rounded-circle"><i class="fas {{ $icon }}"></i></span></td>
                                    <td class="align-middle"><div class="font-weight-bold text-dark small">{{ $plate }}</div><div class="small text-muted" style="font-size: 0.75rem;">{{ $user }}</div></td>
                                    <td class="text-right pr-4 align-middle"><div class="small font-weight-bold">{{ $log->created_at->format('H:i') }}</div><div class="small text-muted">{{ $log->created_at->diffForHumans() }}</div></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- 1. Load SweetAlert (WAJIB) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Variabel Global Notifikasi
    var lastPendingCount = -1; 

    function formatRupiah(angka) { return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0); }
    function formatTimeAgo(dateString) { return new Date(dateString).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}); }

    // --- Chart Init (Code sama seperti sebelumnya) ---
    var ctx = document.getElementById("adminActivityChart");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: { labels: @json($chartLabels), datasets: [{ label: "Masuk", backgroundColor: "rgba(28, 200, 138, 0.05)", borderColor: "#1cc88a", data: @json($chartCheckIns) }, { label: "Keluar", backgroundColor: "rgba(54, 185, 204, 0.05)", borderColor: "#36b9cc", data: @json($chartCheckOuts) }] },
        options: { maintainAspectRatio: false, layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } }, scales: { xAxes: [{ gridLines: { display: false } }], yAxes: [{ ticks: { beginAtZero: true } }] }, legend: { display: true, position: 'top' } }
    });

    // --- UPDATE DASHBOARD & NOTIFIKASI ---
    function updateDashboard(params = '') {
        $.ajax({
            url: "{{ route('admin.data.stats') }}" + params,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // 1. Update Kartu Statistik
                if(response.stats) {
                    $('#total-traffic').text(response.stats.total_traffic);
                    $('#vehicles-inside').text(response.stats.vehicles_inside);
                    $('#revenue-month').text(formatRupiah(response.stats.revenue_month));
                    $('#total-members').text(response.stats.total_members);

                    // ===========================================
                    // == LOGIKA NOTIFIKASI (ALERT) ==
                    // ===========================================
                }

                // 2. Update Tabel Log
                var logBody = $('#recent-logs-body');
                logBody.empty();
                if (!response.recentLogs || response.recentLogs.length === 0) {
                    logBody.append('<tr><td colspan="3" class="text-center py-4 text-muted">Tidak ada aktivitas.</td></tr>');
                } else {
                    $.each(response.recentLogs, function(index, log) {
                        var badgeColor = 'secondary'; var icon = 'fa-question';
                        if(log.status.includes('Masuk')) { badgeColor = 'success'; icon = 'fa-arrow-down'; }
                        else if(log.status.includes('Keluar')) { badgeColor = 'info'; icon = 'fa-arrow-up'; }
                        else { badgeColor = 'danger'; icon = 'fa-times'; }

                        var plate = log.truck_id ? (log.truck.license_plate || 'N/A') : (log.license_plate || 'N/A');
                        var user = (log.truck_id && log.truck && log.truck.user) ? log.truck.user.name : ((log.user && log.user.name) ? log.user.name : 'Guest');
                        var time = new Date(log.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

                        var row = `<tr><td class="pl-4 align-middle"><span class="badge badge-soft-${badgeColor} p-2 rounded-circle"><i class="fas ${icon}"></i></span></td><td class="align-middle"><div class="font-weight-bold text-dark small">${plate}</div><div class="small text-muted" style="font-size: 0.75rem;">${user}</div></td><td class="text-right pr-4 align-middle"><div class="small font-weight-bold">${time}</div></td></tr>`;
                        logBody.append(row);
                    });
                }
            },
            complete: function() { $('.btn-filter').prop('disabled', false).html('<i class="fas fa-filter mr-1"></i> Filter'); }
        });

        // Update Chart (Kode sama...)
        var urlParams = new URLSearchParams(params);
        $.ajax({
            url: "{{ route('admin.chart.filter') }}", type: 'GET', 
            data: { period: urlParams.get('filter') || 'week', start_date: urlParams.get('start_date'), end_date: urlParams.get('end_date') },
            success: function(res) {
                myLineChart.data.labels = res.labels; myLineChart.data.datasets[0].data = res.checkIns; myLineChart.data.datasets[1].data = res.checkOuts; myLineChart.update();
                var labelMap = {'today': 'Hari Ini', 'week': '7 Hari Terakhir', 'month': 'Bulan Ini', 'custom': 'Rentang Dipilih'};
                $('#chart-label-period').text(labelMap[urlParams.get('filter') || 'week'] || 'Custom');
            }
        });
    }

    $(document).ready(function() {
        flatpickr("#flatpickr-range", { mode: "range", dateFormat: "Y-m-d", onChange: function(selectedDates, dateStr, instance) { if (selectedDates.length === 2) { $('#start_date').val(instance.formatDate(selectedDates[0], "Y-m-d")); $('#end_date').val(instance.formatDate(selectedDates[1], "Y-m-d")); } } });
        $('#filter').change(function() { if ($(this).val() === 'custom') $('#custom-date-range').show(); else $('#custom-date-range').hide(); });
        $('#dashboard-filter-form').on('submit', function(e) { e.preventDefault(); updateDashboard('?' + $(this).serialize()); });

        updateDashboard();
        setInterval(function() { if (!$('#license_plate').val()) { updateDashboard(); } }, 5000);
    });
</script>
@endpush