@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Truk Saya</h1>
    <a href="{{ route('member.trucks.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Daftarkan Truk Baru
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
        <h6 class="m-0 font-weight-bold text-primary">Daftar Truk Terdaftar</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Plat Nomor</th>
                        <th>Nama Supir</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($trucks as $truck)
                        <tr>
                            <td>{{ $truck->license_plate }}</td>
                            <td>{{ $truck->driver_name ?? '-' }}</td>
                            <td>
                                @if ($truck->is_inside)
                                    <span class="badge badge-success">Di Dalam Gudang</span>
                                @else
                                    <span class="badge badge-secondary">Di Luar</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('member.trucks.edit', $truck->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <!-- <form action="{{ route('member.trucks.destroy', $truck->id) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus truk ini? Semua QR Code terkait akan ikut terhapus.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form> -->
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Anda belum mendaftarkan truk.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $trucks->links('vendor.pagination.bootstrap-4') }}
        </div>

    </div>
</div>

@endsection