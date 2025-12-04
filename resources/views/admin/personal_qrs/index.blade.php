@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen QR Pribadi</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Member & Aset QR</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Member</th>
                        <th>Nama Member</th>
                        <th>Email</th>
                        <th>Jumlah QR Pribadi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td>{{ $member->id }}</td>
                            <td><strong>{{ $member->name }}</strong></td>
                            <td>{{ $member->email }}</td>
                            <td>
                                <span class="badge badge-info" style="font-size: 1rem;">
                                    {{ $member->personal_qrs_count }} Unit
                                </span>
                            </td>
                            <td>
                                {{-- Tombol Detail ke Halaman List QR --}}
                                <a href="{{ route('admin.personal-qrs.member', $member->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-list"></i> Detail / Edit QR
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Belum ada member terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $members->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

@endsection