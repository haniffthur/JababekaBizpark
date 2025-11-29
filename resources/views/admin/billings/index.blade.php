@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Keuangan</h1>
    <a href="{{ route('admin.billings.create') }}" class="btn btn-sm btn-primary shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50"></i> Buat Tagihan Manual
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
                        <th>Bukti</th>
                        <th>Jatuh Tempo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billings as $billing)
                        <tr>
                            <td>#{{ $billing->id }}</td>
                            <td>{{ $billing->user->name ?? 'Member Dihapus' }}</td>
                            <td>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if ($billing->status == 'paid')
                                    <span class="badge badge-success">Lunas (Paid)</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>
    @if ($bill->proof_image)
        <a href="{{ asset('storage/' . $bill->proof_image) }}" target="_blank">
            <img src="{{ asset('storage/' . $bill->proof_image) }}" alt="Bukti" style="height: 50px;">
        </a>
    @else
        -
    @endif
</td>
                            <td>{{ $billing->due_date->format('d/m/Y') }}</td>
                            <td style="width: 200px;">
                                <a href="{{ route('admin.billings.show', $billing->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.billings.edit', $billing->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.billings.destroy', $billing->id) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus tagihan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                            
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada data tagihan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center">
            {{ $billings->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

@endsection