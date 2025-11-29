@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Mesin Gerbang</h1>
    <a href="{{ route('admin.gate-machines.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Mesin Baru
    </a>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Terminal Terdaftar</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Terminal No (ID)</th>
                        <th>Lokasi</th>
                        <th>Fungsi (Otomatis)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($machines as $machine)
                        @php
                            // Logika Ganjil = Masuk, Genap = Keluar
                            $mode = intval($machine->termno) % 2 != 0 ? 'GERBANG MASUK (IN)' : 'GERBANG KELUAR (OUT)';
                            $badge = intval($machine->termno) % 2 != 0 ? 'badge-success' : 'badge-danger';
                        @endphp
                        <tr>
                            <td>{{ $machine->id }}</td>
                            <td><strong>{{ $machine->termno }}</strong></td>
                            <td>{{ $machine->location }}</td>
                            <td><span class="badge {{ $badge }}">{{ $mode }}</span></td>
                            <td>
                                <a href="{{ route('admin.gate-machines.edit', $machine->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.gate-machines.destroy', $machine->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus mesin ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Belum ada mesin terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $machines->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@endsection