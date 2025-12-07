@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Keuangan</h1>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Semua Tagihan Member</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Bukti Bayar</th>
                        <th>Jatuh Tempo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="billing-table-body">
        @include('admin.billings.partials.table_body')
    </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $billings->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@push('scripts')
<script>
    function refreshBillingTable() {
        $.ajax({
            url: "{{ route('admin.billings.index') }}", // Panggil route index biasa
            type: 'GET',
            success: function(responseHtml) {
                // Ganti isi tabel dengan HTML baru dari controller
                $('#billing-table-body').html(responseHtml);
            },
            error: function(xhr) { console.log("Gagal refresh billing admin"); }
        });
    }

    $(document).ready(function() {
        // Refresh otomatis setiap 5 detik
        setInterval(refreshBillingTable, 5000);
    });
</script>
@endpush
@endsection