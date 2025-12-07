@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Persetujuan QR Pribadi</h1>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif

<div class="card shadow mb-4 border-left-warning">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-warning">Daftar Permintaan Menunggu Persetujuan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th>Member</th>
                        <th>Label Kendaraan</th>
                        <th>Plat Nomor</th>
                        <th>Waktu Request</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingRequests as $req)
                    <tr>
                        <td>
                            <span class="font-weight-bold">{{ $req->user->name ?? '-' }}</span><br>
                            <small class="text-muted">{{ $req->user->email ?? '' }}</small>
                        </td>
                        <td>{{ $req->name }}</td>
                        <td>
                            <span class="badge badge-light text-dark border px-2 py-1" style="font-size: 0.9rem;">
                                {{ $req->license_plate }}
                            </span>
                        </td>
                        <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <form action="{{ route('admin.personal-qrs.approve', $req->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-success btn-sm shadow-sm">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.personal-qrs.reject', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak permintaan ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm shadow-sm">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-3 text-gray-300"></i><br>
                            Tidak ada permintaan baru saat ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection