@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen QR Pribadi Member</h1>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Semua QR Code Pribadi (Reusable)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pemilik (Member)</th>
                        <th>Nama Slot</th>
                        <th>Plat Nomor Terikat</th>
                        <th>Kode QR</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="personal-qr-table-body">
                    
                    {{-- Konten pertama kali dimuat oleh Blade --}}
                    @forelse ($personalQrs as $qr)
                        <tr>
                            <td>{{ $qr->id }}</td>
                            <td>{{ $qr->user->name ?? 'N/A' }}</td>
                            <td>{{ $qr->name }}</td>
                            <td><strong>{{ $qr->license_plate }}</strong></td>
                            <td><code>{{ $qr->code }}</code></td>
                            <td>
                                @if ($qr->status == 'aktif')
                                    <span class="badge badge-success">Di Dalam</span>
                                @else
                                    <span class="badge badge-secondary">Di Luar</span>
                                @endif
                            </td>
                            <td>
                                {{-- TOMBOL EDIT BARU --}}
                                <a href="{{ route('admin.personal-qrs.edit', $qr->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada QR Code Pribadi yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div id="pagination-links" class="d-flex justify-content-center">
            {{ $personalQrs->links('vendor.pagination.bootstrap-4') }}
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Fungsi helper untuk status
function getPersonalStatusBadge(status) {
    if (status == 'aktif') {
        return '<span class="badge badge-success">Di Dalam</span>';
    } else {
        return '<span class="badge badge-secondary">Di Luar</span>';
    }
}

function fetchPersonalQrData() {
    $.ajax({
        url: "{{ route('admin.personal-qrs.index') }}", 
        type: 'GET',
        dataType: 'json', 
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            var tableBody = $('#personal-qr-table-body');
            tableBody.empty();
            $('#pagination-links').hide(); 

            if (!response.data || response.data.length === 0) {
                tableBody.append('<tr><td colspan="7" class="text-center">Belum ada QR Code Pribadi yang terdaftar.</td></tr>');
                return;
            }

            $.each(response.data, function(index, qr) {
                // UPDATE TOMBOL EDIT DI AJAX
                var editUrl = "{{ url('admin/personal-qrs') }}/" + qr.id + "/edit";
                var actionBtns = '<a href="' + editUrl + '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Edit</a>';
                
                var row = '<tr>' +
                    '<td>' + qr.id + '</td>' +
                    '<td>' + (qr.user ? qr.user.name : 'N/A') + '</td>' +
                    '<td>' + qr.name + '</td>' +
                    '<td><strong>' + qr.license_plate + '</strong></td>' +
                    '<td><code>' + qr.code + '</code></td>' +
                    '<td>' + getPersonalStatusBadge(qr.status) + '</td>' +
                    '<td>' + actionBtns + '</td>' +
                    '</tr>';
                tableBody.append(row);
            });
        },
        error: function(xhr, status, error) {
            console.error("Gagal memuat data QR Pribadi:", error);
        }
    });
}

$(document).ready(function() {
    setInterval(fetchPersonalQrData, 5000); 
});
</script>
@endpush