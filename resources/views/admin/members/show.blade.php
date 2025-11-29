@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Member: {{ $member->name }}</h1>
    <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
            </div>
            <div class="card-body">
                <strong>Nama:</strong>
                <p class="mb-2">{{ $member->name }}</p>

                <strong>Email:</strong>
                <p class="mb-2">{{ $member->email }}</p>

                <strong>Bergabung pada:</strong>
                <p class="mb-0">{{ $member->created_at->format('d F Y') }}</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Truk Milik Member</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Plat Nomor</th>
                                <th>Nama Supir</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($member->trucks as $truck)
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">Member ini belum mendaftarkan truk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Riwayat Tagihan Member</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Tagihan</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Jatuh Tempo</th>
                                <th>Tgl. Dibuat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($member->billings as $billing)
                                <tr>
                                    <td>#{{ $billing->id }}</td>
                                    <td>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($billing->status == 'paid')
                                            <span class="badge badge-success">Lunas</span>
                                        @else
                                            <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $billing->due_date->format('d/m/Y') }}</td>
                                    <td>{{ $billing->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada riwayat tagihan untuk member ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection