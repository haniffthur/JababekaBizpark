@extends('layouts.app')

@section('content')

{{-- CSS Custom (Sama seperti Admin agar konsisten) --}}
@push('styles')
<style>
    .badge-soft-success { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
    .badge-soft-info { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }
    .badge-soft-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .badge-soft-danger { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
    .badge-soft-secondary { background-color: #e2e3e5; color: #41464b; border: 1px solid #d3d6d8; }
    
    .badge-pill { padding: 0.5em 1em; border-radius: 50rem; font-weight: 600;}
    
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

    .table-modern th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; color: #b0b3c5; }
    .table-modern td { vertical-align: middle; border-bottom: 1px solid #f8f9fc; padding: 1rem 0.75rem; }
</style>
@endpush

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800 font-weight-bold">Halo, {{ Auth::user()->name }}!</h1>
        <p class="mb-0 text-muted small">Pantau aktivitas armada dan tagihan Anda di sini.</p>
    </div>
    <div>
        <span class="badge badge-pill bg-white text-primary shadow-sm px-3 py-2">
            <i class="fas fa-clock mr-1"></i> <span id="live-clock">Loading...</span>
        </span>
    </div>
</div>

{{-- INFO CARDS --}}
<div class="row mb-4">
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-clean h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Armada</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="total-trucks">{{ $emptyStats['total_trucks'] ?? 0 }}</div>
                        <small class="text-muted">Truk Terdaftar</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-primary-soft">
                            <i class="fas fa-truck"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sedang Di Dalam</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="trucks-inside">{{ $emptyStats['trucks_inside'] ?? 0 }}</div>
                        <small class="text-muted">Unit Parkir</small>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">QR Code Aktif</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="active-qrs">{{ $emptyStats['active_qrs'] ?? 0 }}</div>
                        <small class="text-muted">Siap Digunakan</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-info-soft">
                            <i class="fas fa-qrcode"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Tagihan Pending</div>
                        <div class="h4 mb-0 font-weight-bold text-gray-800" id="pending-billings">
                            Rp {{ number_format($emptyStats['pending_billings'] ?? 0, 0, ',', '.') }}
                        </div>
                        <small class="text-muted">Belum Lunas</small>
                    </div>
                    <div class="col-auto">
                        <div class="icon-box bg-gradient-warning-soft">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card card-clean shadow-sm">
            <div class="card-header py-3 bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Status Armada Saya (Live)</h6>
                <a href="{{ route('member.trucks.index') }}" class="btn btn-sm btn-light text-primary font-weight-bold">Kelola Truk &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0" width="100%">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">Plat Nomor</th>
                                <th>Supir</th>
                                <th>Status Lokasi</th>
                            </tr>
                        </thead>
                        <tbody id="truck-status-body">
                            {{-- Initial Data --}}
                            @forelse ($myTruckStatus as $truck)
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $truck->license_plate }}</td>
                                    <td class="text-muted">{{ $truck->driver_name ?? '-' }}</td>
                                    <td>
                                        @if ($truck->is_inside)
                                            <span class="badge badge-soft-success badge-pill"><i class="fas fa-check mr-1"></i> Di Dalam</span>
                                        @else
                                            <span class="badge badge-soft-secondary badge-pill"><i class="fas fa-road mr-1"></i> Di Luar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-5 text-muted">Belum ada data truk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card card-clean shadow-sm h-100">
            <div class="card-header py-3 bg-white border-bottom-0">
                <h6 class="m-0 font-weight-bold text-primary">Distribusi Armada</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-2 pb-2">
                    <canvas id="memberPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2"><i class="fas fa-circle text-success"></i> Di Dalam</span>
                    <span class="mr-2"><i class="fas fa-circle text-secondary"></i> Di Luar</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script>
// Clock
setInterval(() => { document.getElementById('live-clock').innerText = new Date().toLocaleTimeString('id-ID'); }, 1000);

// Format Rupiah
function formatRupiah(angka) {
    var num = parseFloat(angka);
    if (isNaN(num)) return 'Rp 0';
    return 'Rp ' + num.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// --- CHART CONFIG ---
var ctxMember = document.getElementById("memberPieChart");
var memberPieChart = new Chart(ctxMember, {
    type: 'doughnut',
    data: {
        labels: ["Di Dalam", "Di Luar"],
        datasets: [{
            data: @json($pieChartData),
            backgroundColor: ['#1cc88a', '#e2e3e5'],
            hoverBackgroundColor: ['#17a673', '#d1d3d5'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
            borderWidth: 0
        }],
    },
    options: {
        maintainAspectRatio: false,
        tooltips: { backgroundColor: "rgb(255,255,255)", bodyFontColor: "#858796", borderColor: '#dddfeb', borderWidth: 1, xPadding: 15, yPadding: 15, displayColors: false, caretPadding: 10 },
        legend: { display: false },
        cutoutPercentage: 75,
    },
});

// --- AJAX POLLING ---
function updateMemberDashboard() {
    $.ajax({
        url: "{{ route('member.data.stats') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Cards
            $('#total-trucks').text(response.stats.total_trucks);
            $('#trucks-inside').text(response.stats.trucks_inside);
            $('#active-qrs').text(response.stats.active_qrs);
            $('#pending-billings').text(formatRupiah(response.stats.pending_billings));

            // Chart
            if (memberPieChart && response.stats.total_trucks > 0) {
                 memberPieChart.data.datasets[0].data = [response.stats.trucks_inside, response.stats.total_trucks - response.stats.trucks_inside];
                 memberPieChart.update();
            }

            // Table
            var tableBody = $('#truck-status-body');
            tableBody.empty();
            
            if (!response.myTruckStatus || response.myTruckStatus.length === 0) {
                tableBody.append('<tr><td colspan="3" class="text-center py-5 text-muted">Belum ada data truk.</td></tr>');
                return;
            }

            $.each(response.myTruckStatus, function(index, truck) {
                var statusBadge = truck.is_inside 
                    ? '<span class="badge badge-soft-success badge-pill"><i class="fas fa-check mr-1"></i> Di Dalam</span>' 
                    : '<span class="badge badge-soft-secondary badge-pill"><i class="fas fa-road mr-1"></i> Di Luar</span>';
                
                var row = `
                    <tr>
                        <td class="pl-4"><span class="font-weight-bold text-dark">${truck.license_plate}</span></td>
                        <td class="text-muted small">${truck.driver_name || '-'}</td>
                        <td>${statusBadge}</td>
                    </tr>`;
                tableBody.append(row);
            });
        }
    });
}

$(document).ready(function() {
    setInterval(updateMemberDashboard, 1000); 
});
</script>
@endpush