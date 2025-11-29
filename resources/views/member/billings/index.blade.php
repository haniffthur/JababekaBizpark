@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tagihan Saya</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Daftar Tagihan Pribadi</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Tagihan</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Jatuh Tempo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billings as $billing)
                        <tr>
                            <td>#{{ $billing->id }}</td>
                            <td>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if ($billing->status == 'paid')
                                    <span class="badge badge-success">Lunas (Paid)</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $billing->due_date->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('member.billings.show', $billing->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Anda belum memiliki tagihan.</td>
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