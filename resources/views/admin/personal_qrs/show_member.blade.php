@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail QR: {{ $user->name }}</h1>
    <a href="{{ route('admin.personal-qrs.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar QR Code Milik {{ $user->name }}</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nama Slot</th>
                        <th>Plat Nomor</th>
                        <th>Kode QR (Unik)</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($personalQrs as $qr)
                        <tr>
                            <td>{{ $qr->name }}</td>
                            <td><strong>{{ $qr->license_plate }}</strong></td>
                            <td><code class="text-danger">{{ $qr->code }}</code></td>
                            <td>
                                @if ($qr->status == 'aktif')
                                    <span class="badge badge-success">Di Dalam</span>
                                @else
                                    <span class="badge badge-secondary">Di Luar</span>
                                @endif
                            </td>
                            <td>
                                {{-- Tombol Edit mengarah ke form edit yang sudah ada --}}
                                <a href="{{ route('admin.personal-qrs.edit', $qr->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Member ini belum memiliki QR Code Pribadi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection