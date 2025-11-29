@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard Member</h1>
</div>

<div class="row">

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Truk Saya</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-trucks">{{ $emptyStats['total_trucks'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-truck fa-2x text-gray-300"></i>
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
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            QR Code Aktif</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="active-qrs">{{ $emptyStats['active_qrs'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-qrcode fa-2x text-gray-300"></i>
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
                            Total Tagihan Pending</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800" id="pending-billings">Rp {{ number_format($emptyStats['pending_billings'], 0, ',', '.') }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-invoice-dollar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Status Truk Saya</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="memberPieChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Di Dalam
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-secondary"></i> Di Luar
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Truk Saya (5 Terbaru)</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Plat Nomor</th>
                                <th>Nama Supir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="truck-status-body">
                            {{-- Konten Awal Dimuat Blade --}}
                            @forelse ($myTruckStatus as $truck)
                                <tr>
                                    <td><strong>{{ $truck->license_plate }}</strong></td>
                                    <td>{{ $truck->driver_name ?? '-' }}</td>
                                    <td>
                                        @if ($truck->is_inside)
                                            <span class="badge badge-success">Di Dalam Gudang</span>
                                        @else
                                            <span class="badge badge-secondary">Di Luar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">Memuat data...</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('member.trucks.index') }}" class="small text-primary">Lihat Semua Truk</a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script>
// Helper functions (Rupiah)
function formatRupiah(angka) {
    var num = parseFloat(angka);
    if (isNaN(num)) return 'Rp 0';
    return 'Rp ' + num.toFixed(0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Chart Instance harus global agar bisa di-update
var memberPieChart;

function updateMemberDashboard(pieData) {
    $.ajax({
        url: "{{ route('member.data.stats') }}",
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Update Info Cards
            $('#total-trucks').text(response.stats.total_trucks);
            $('#trucks-inside').text(response.stats.trucks_inside);
            $('#active-qrs').text(response.stats.active_qrs);
            $('#pending-billings').text(formatRupiah(response.stats.pending_billings));

            // Update Chart (jika data berubah)
            if (memberPieChart && response.stats.total_trucks > 0) {
                 memberPieChart.data.datasets[0].data = [response.stats.trucks_inside, response.stats.total_trucks - response.stats.trucks_inside];
                 memberPieChart.update();
            }

            // Update Truck Status Table
            var tableBody = $('#truck-status-body');
            tableBody.empty();
            
            if (response.myTruckStatus.length === 0) {
                tableBody.append('<tr><td colspan="3" class="text-center">Tidak ada truk terdaftar.</td></tr>');
                return;
            }

            $.each(response.myTruckStatus, function(index, truck) {
                var statusBadge = truck.is_inside 
                    ? '<span class="badge badge-success">Di Dalam Gudang</span>' 
                    : '<span class="badge badge-secondary">Di Luar</span>';
                
                var row = '<tr>' +
                    '<td><strong>' + truck.license_plate + '</strong></td>' +
                    '<td>' + (truck.driver_name || '-') + '</td>' +
                    '<td>' + statusBadge + '</td>' +
                    '</tr>';
                tableBody.append(row);
            });
        }
    });
}

$(document).ready(function() {
    // CHART SCRIPT (Inisialisasi)
    var ctxMember = document.getElementById("memberPieChart");
    memberPieChart = new Chart(ctxMember, {
        type: 'doughnut',
        data: {
            labels: ["Di Dalam", "Di Luar"],
            datasets: [{
                data: @json($pieChartData), // Data awal dari Blade
                backgroundColor: ['#1cc88a', '#858796'],
                hoverBackgroundColor: ['#17a673', '#60616f'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            // ... (options lain) ...
            cutoutPercentage: 80,
        },
    });

    // Jalankan pertama kali
    updateMemberDashboard();
    
    // Polling setiap 5 detik
    setInterval(updateMemberDashboard, 5000); 
});
</script>
@endpush