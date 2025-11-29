@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Member</h1>
    <a href="{{ route('admin.members.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Member Baru
    </a>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Member (Pemilik Toko)</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status IPL</th> {{-- <-- KOLOM BARU --}}
                        <th>Tgl. Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="member-table-body">
                    @forelse ($members as $member)
                        <tr>
                            <td>{{ $member->id }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>
                                {{-- KONTEN BARU --}}
                                @if ($member->ipl_status == 'paid')
                                    <span class="badge badge-success">Sudah Bayar</span>
                                @else
                                    <span class="badge badge-danger">Belum Bayar</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.members.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus member ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data member.</td> {{-- Colspan jadi 6 --}}
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div id="pagination-links" class="d-flex justify-content-center">
            {{ $members->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Fungsi helper untuk status
function getIplStatusBadge(status) {
    if (status == 'paid') {
        return '<span class="badge badge-success">Sudah Bayar</span>';
    }
    return '<span class="badge badge-danger">Belum Bayar</span>';
}

function updateMemberTable(url = "{{ route('admin.members.data') }}") {
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(response) {
            var tableBody = $('#member-table-body');
            tableBody.empty();
            
            if (!response.data || response.data.length === 0) {
                tableBody.append('<tr><td colspan="6" class="text-center">Belum ada data member.</td></tr>');
                $('#pagination-links').hide();
                return;
            }

            $.each(response.data, function(index, member) {
                var showUrl = "{{ url('admin/members') }}/" + member.id;
                var editUrl = "{{ url('admin/members') }}/" + member.id + "/edit";
                var deleteUrl = "{{ url('admin/members') }}/" + member.id;
                var csrfToken = "{{ csrf_token() }}";
                
                var actionBtns = 
                    '<a href="' + showUrl + '" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a> ' +
                    '<a href="' + editUrl + '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a> ' +
                    '<form action="' + deleteUrl + '" method="POST" class="d-inline" onsubmit="return confirm(\'Yakin hapus member ini?\');">' +
                    '<input type="hidden" name="_token" value="' + csrfToken + '">' +
                    '<input type="hidden" name="_method" value="DELETE">' +
                    '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>' +
                    '</form>';
                
                var iplStatus = getIplStatusBadge(member.ipl_status);

                var row = '<tr>' +
                    '<td>' + member.id + '</td>' +
                    '<td>' + member.name + '</td>' +
                    '<td>' + member.email + '</td>' +
                    '<td>' + iplStatus + '</td>' + // <-- BARIS BARU DI AJAX
                    '<td>' + new Date(member.created_at).toLocaleDateString('id-ID') + '</td>' +
                    '<td>' + actionBtns + '</td>' +
                    '</tr>';
                tableBody.append(row);
            });
            
            // Tampilkan paginasi jika ada
            if (response.pagination) {
                $('#pagination-links').html(response.pagination).show();
            } else {
                $('#pagination-links').hide();
            }
        }
    });
}

$(document).ready(function() {
    setInterval(function() {
        var currentPageUrl = $('#pagination-links .active a').attr('href') || "{{ route('admin.members.data') }}";
        updateMemberTable(currentPageUrl);
    }, 5000); 

    $(document).on('click', '#pagination-links .pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        updateMemberTable(url);
    });
});
</script>
@endpush