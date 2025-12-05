@extends('layouts.app')

@section('content')

{{-- 1. CSS KHUSUS (MODERN UI) --}}
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* --- Modern Filter Bar --- */
    .filter-bar {
        background: #fff;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.03);
        border: 1px solid #f1f3f9;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        align-items: center;
        background: #f8f9fc;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        border: 1px solid #eaecf4;
        flex: 1;
        min-width: 200px;
    }
    .filter-group i { color: #b7b9cc; margin-right: 10px; }
    .filter-input {
        border: none;
        background: transparent;
        width: 100%;
        font-size: 0.9rem;
        color: #5a5c69;
        outline: none;
    }
    .filter-input:focus { outline: none; }
    .btn-filter {
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(78, 115, 223, 0.2);
        transition: all 0.2s;
    }
    .btn-filter:hover { transform: translateY(-1px); box-shadow: 0 6px 8px rgba(78, 115, 223, 0.3); }

    /* --- Soft Badges --- */
    .badge-soft-success { background-color: #d1e7dd; color: #0f5132; }
    .badge-soft-info { background-color: #cff4fc; color: #055160; }
    .badge-soft-danger { background-color: #f8d7da; color: #842029; }
    .badge-pill { padding: 0.5em 1em; border-radius: 50rem; }

    /* --- Clean Cards --- */
    .card-clean {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    .card-clean:hover { transform: translateY(-3px); }
    .icon-box {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }
    .bg-gradient-primary-soft { background: linear-gradient(135deg, rgba(78,115,223,0.1) 0%, rgba(78,115,223,0.2) 100%); color: #4e73df; }
    .bg-gradient-success-soft { background: linear-gradient(135deg, rgba(28,200,138,0.1) 0%, rgba(28,200,138,0.2) 100%); color: #1cc88a; }
    .bg-gradient-info-soft    { background: linear-gradient(135deg, rgba(54,185,204,0.1) 0%, rgba(54,185,204,0.2) 100%); color: #36b9cc; }
    .bg-gradient-warning-soft { background: linear-gradient(135deg, rgba(246,194,62,0.1) 0%, rgba(246,194,62,0.2) 100%); color: #f6c23e; }
    
    /* Table Styling */
    .table-modern td { vertical-align: middle; }
    .avatar-initial {
        width: 35px; height: 35px;
        background-color: #eaecf4;
        color: #4e73df;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold;
        font-size: 0.9rem;
    }
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
        <a href="#" class="btn btn-sm btn-light shadow-sm" onclick="updateDashboard(); return false;">
            <i class="fas fa-sync-alt fa-sm text-gray-400"></i>
        </a>
    </div>
</div>

{{-- 2. FILTER BAR PROFESIONAL --}}
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
        
        {{-- Custom Date (Hidden Default) --}}
        <div class="filter-group" id="custom-date-range" style="display: none;">
            <i class="fas fa-clock"></i>
            <input type="text" id="flatpickr-range" class="filter-input" placeholder="Pilih Rentang Tanggal">
            <input type="hidden" name="start_date" id="start_date">
            <input type="hidden" name="end_date" id="end_date">
        </div>

        <div class="filter-group">
            <i class="fas fa-car"></i>
            <input type="text" name="license_plate" class="filter-input" placeholder="Cari Plat Nomor...">
        </div>

        <div class="filter-group">
            <i class="fas fa-info-circle"></i>
            <select name="status_filter" id="status_filter" class="filter-input">
                <option value="">Semua Status</option>
                <option value="success">Berhasil</option>
                <option value="failed">Gagal/Ditolak</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary btn-filter">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
    </div>
</form>

{{-- 3. INFO CARDS --}}
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Traffic</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="total-traffic">
                            {{ $emptyStats['total_traffic'] ?? 0 }}
                        </div>
                        <small class="text-muted" id="traffic-label">Hari Ini</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-primary-soft">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Di Dalam Gudang</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="vehicles-inside">
                            {{ $emptyStats['vehicles_inside'] ?? 0 }}
                        </div>
                        <small class="text-muted">Truk & Pribadi</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-success-soft">
                            <i class="fas fa-warehouse"></i>
                        </div>
                    </div>
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
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="revenue-month">
                            Rp {{ number_format($emptyStats['revenue_month'] ?? 0, 0, ',', '.') }}
                        </div>
                        <small class="text-muted">Tagihan IPL Bulan Ini</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-info-soft">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
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
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="total-members">
                            {{ $emptyStats['total_members'] ?? 0 }}
                        </div>
                        <small class="text-muted">Pengguna Terdaftar</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-warning-soft">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. CHART & LOGS --}}
<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card card-clean mb-4">
            <div class="card-header py-3 bg-white border-bottom-0 d-flex justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Statistik <span id="chart-label-period">7 Hari Terakhir</span></h6>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 320px;">
                    <canvas id="adminActivityChart"></canvas>
                </div>
            </div>
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
                        <thead class="bg-light text-muted small">
                            <tr>
                                <th class="pl-4">Status</th>
                                <th>Kendaraan</th>
                                <th class="text-right pr-4">Waktu</th>
                            </tr>
                        </thead>
                        <tbody id="recent-logs-body">
                            {{-- Initial Data dari Blade --}}
                            @forelse ($recentLogs as $log)
                                @php
                                    $badgeColor = 'secondary'; $icon = 'fa-question';
                                    if(str_contains($log->status, 'Masuk')) { $badgeColor = 'success'; $icon = 'fa-arrow-down'; }
                                    elseif(str_contains($log->status, 'Keluar')) { $badgeColor = 'info'; $icon = 'fa-arrow-up'; }
                                    else { $badgeColor = 'danger'; $icon = 'fa-times'; }

                                    $plate = $log->truck_id ? ($log->truck->license_plate ?? 'N/A') : ($log->license_plate ?? 'N/A');
                                    $userName = 'Guest';
                                    if ($log->truck_id && $log->truck && $log->truck->user) { 
                                        $userName = $log->truck->user->name; 
                                    } elseif ($log->user_id && $log->user) { 
                                        $userName = $log->user->name; 
                                    }
                                @endphp
                                <tr>
                                    <td class="pl-4 align-middle">
                                        <span class="badge badge-soft-{{ $badgeColor }} p-2 rounded-circle">
                                            <i class="fas {{ $icon }}"></i>
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <div class="font-weight-bold text-dark small">{{ $plate }}</div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">{{ $userName }}</div>
                                    </td>
                                    <td class="text-right pr-4 align-middle">
                                        <div class="small font-weight-bold">{{ $log->created_at->format('H:i') }}</div>
                                        <div class="small text-muted">{{ $log->created_at->diffForHumans() }}</div>
                                    </td>
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

{{-- 5. JAVASCRIPT LOGIC --}}
@push('scripts')
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<audio id="notif-sound" src="{{ asset('audio/notif.mp3') }}" preload="auto"></audio>

<script>

    // Variabel Global
    var lastPendingCount = -1;
    var isSoundEnabled = false; // Status izin suara

    // Helper Functions (Sama seperti sebelumnya)
    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0);
    }
    function formatTimeAgo(dateString) {
        const d = new Date(dateString);
        return d.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
    }

    // --- FUNGSI IZIN SUARA ---
    function enableSound() {
        var audio = document.getElementById('notif-sound');
        // Putar sebentar lalu pause untuk "memancing" izin browser
        audio.play().then(() => {
            audio.pause();
            audio.currentTime = 0;
            isSoundEnabled = true;
            $('#btn-enable-sound').fadeOut(); // Sembunyikan tombol jika sukses
            console.log("Audio diaktifkan!");
        }).catch(error => {
            console.error("Gagal mengaktifkan audio:", error);
            alert("Browser memblokir audio. Silakan cek pengaturan situs.");
        });
    }
    // Helper: Format Rupiah
    function formatRupiah(angka) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0);
    }

    // Helper: Chart Init
    var ctx = document.getElementById("adminActivityChart");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: "Masuk",
                lineTension: 0.4,
                backgroundColor: "rgba(28, 200, 138, 0.05)",
                borderColor: "#1cc88a",
                pointRadius: 0,
                pointHoverRadius: 5,
                borderWidth: 2,
                data: @json($chartCheckIns)
            }, {
                label: "Keluar",
                lineTension: 0.4,
                backgroundColor: "rgba(54, 185, 204, 0.05)",
                borderColor: "#36b9cc",
                pointRadius: 0,
                pointHoverRadius: 5,
                borderWidth: 2,
                data: @json($chartCheckOuts)
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
            scales: {
                xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 7 } }],
                yAxes: [{ ticks: { maxTicksLimit: 5, padding: 10, beginAtZero: true }, gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] } }]
            },
            legend: { display: true, position: 'top' },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10
            }
        }
    });

    // --- AJAX UPDATE FUNCTION ---
   function updateDashboard(params = '') {
        // Jangan disable tombol filter saat polling otomatis background
        if(params) {
            $('.btn-filter').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
        }

        $.ajax({
            url: "{{ route('admin.data.stats') }}" + params,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // 1. Update Cards
                if(response.stats) {
                    $('#total-traffic').text(response.stats.total_traffic);
                    $('#vehicles-inside').text(response.stats.vehicles_inside);
                    $('#revenue-month').text(formatRupiah(response.stats.revenue_month));
                    $('#total-members').text(response.stats.total_members);

                    // ==================================================
                    // == LOGIKA NOTIFIKASI SUARA & SIDEBAR REAL-TIME ==
                    // ==================================================
                    var currentPending = response.stats.pending_qr_count;
                    
                    // A. Update Angka di Sidebar
                    var badge = $('#sidebar-pending-badge');
                    if (currentPending > 0) {
                        badge.text(currentPending).show();
                    } else {
                        badge.hide();
                    }

                    // B. Mainkan Suara "Ting!" jika ada tambahan permintaan
                    if (lastPendingCount !== -1 && currentPending > lastPendingCount) {
                        if (isSoundEnabled) {
                            var audio = document.getElementById('notif-sound');
                            audio.currentTime = 0;
                            audio.play().catch(e => console.log("Audio error:", e));
                        }
                    }
                    
                    // Update data terakhir
                    lastPendingCount = currentPending;
                }

                // 2. Update Logs Table
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
                        var user = (log.truck_id && log.truck && log.truck.user) ? log.truck.user.name : (log.user ? log.user.name : 'Guest');
                        var time = new Date(log.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});

                        var row = `<tr>
                            <td class="pl-4 align-middle"><span class="badge badge-soft-${badgeColor} p-2 rounded-circle"><i class="fas ${icon}"></i></span></td>
                            <td class="align-middle"><div class="font-weight-bold text-dark small">${plate}</div><div class="small text-muted" style="font-size: 0.75rem;">${user}</div></td>
                            <td class="text-right pr-4 align-middle"><div class="small font-weight-bold">${time}</div></td>
                        </tr>`;
                        logBody.append(row);
                    });
                }
            },
            complete: function() {
                $('.btn-filter').prop('disabled', false).html('<i class="fas fa-filter mr-1"></i> Filter');
            }
        });
    

        // Update Chart jika filter period
        var urlParams = new URLSearchParams(params);
        var period = urlParams.get('filter') || 'week';
        var start = urlParams.get('start_date');
        var end = urlParams.get('end_date');

        $.ajax({
            url: "{{ route('admin.chart.filter') }}",
            type: 'GET',
            data: { period: period, start_date: start, end_date: end },
            success: function(res) {
                myLineChart.data.labels = res.labels;
                myLineChart.data.datasets[0].data = res.checkIns;
                myLineChart.data.datasets[1].data = res.checkOuts;
                myLineChart.update();
                
                var labelMap = {'today': 'Hari Ini', 'week': '7 Hari Terakhir', 'month': 'Bulan Ini', 'custom': 'Rentang Dipilih'};
                $('#chart-label-period').text(labelMap[period] || 'Custom');
            }
        });
    }

    // --- Event Listeners ---
   $(document).ready(function() {
        // Flatpickr & Filter Logic (Sama)
        flatpickr("#flatpickr-range", { mode: "range", dateFormat: "Y-m-d", onChange: function(selectedDates, dateStr, instance) { if (selectedDates.length === 2) { $('#start_date').val(instance.formatDate(selectedDates[0], "Y-m-d")); $('#end_date').val(instance.formatDate(selectedDates[1], "Y-m-d")); } } });
        $('#filter').change(function() { if ($(this).val() === 'custom') $('#custom-date-range').show(); else $('#custom-date-range').hide(); });
        $('#dashboard-filter-form').on('submit', function(e) { e.preventDefault(); const formData = $(this).serialize(); updateDashboard('?' + formData); });

        // Tambahkan Tombol Aktivasi Suara di Header
        var headerDiv = $('.d-sm-flex.align-items-center.justify-content-between.mb-4').first();
        var btnSound = `
            <button id="btn-enable-sound" class="btn btn-sm btn-warning shadow-sm mr-2" onclick="enableSound()">
                <i class="fas fa-bell"></i> Aktifkan Suara Notifikasi
            </button>
        `;
        // Sisipkan tombol sebelum tombol Refresh
        headerDiv.find('a').before(btnSound);

        // Auto Poll setiap 5 detik
        setInterval(function() {
            // Hanya auto update jika user TIDAK sedang mengetik filter
            if (!$('#license_plate').val()) {
                 updateDashboard(); 
            }
        }, 1000);
    });
</script>
@endpush