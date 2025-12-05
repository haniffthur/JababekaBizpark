@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Member: {{ $member->name }}</h1>
    <div>
        <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
        </a>
        <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-sm btn-info shadow-sm">
            <i class="fas fa-edit fa-sm text-white-50"></i> Edit Member & QR
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Informasi Akun</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Nama Lengkap:</strong>
                        <p>{{ $member->name }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Email:</strong>
                        <p>{{ $member->email }}</p>
                    </div>
                    <div class="col-md-2">
                        <strong>Status IPL:</strong>
                        <p>
                            @if($member->ipl_status == 'paid')
                                <span class="badge badge-success">Lunas (Paid)</span>
                            @else
                                <span class="badge badge-danger">Belum Bayar</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-2">
                        <strong>Bergabung Sejak:</strong>
                        <p>{{ $member->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success">QR Code Pribadi (4 Slot)</h6>
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
                                <th>Terakhir Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($member->personalQrs as $qr)
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
                                    <td>{{ $qr->updated_at->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada QR Code Pribadi yang didaftarkan.</td>
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

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Armada Truk ({{ $member->trucks->count() }})</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Plat Nomor</th>
                                <th>Supir</th>
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
                                            <span class="badge badge-success">Di Dalam</span>
                                        @else
                                            <span class="badge badge-secondary">Di Luar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center small">Tidak ada truk terdaftar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Tagihan Terakhir</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tgl</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Ambil 5 tagihan terakhir saja --}}
                            @forelse ($member->billings->sortByDesc('created_at')->take(5) as $bill)
                                <tr>
                                    <td>#{{ $bill->id }}</td>
                                    <td>Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($bill->status == 'paid')
                                            <span class="badge badge-success">Lunas</span>
                                        @else
                                            <span class="badge badge-danger">Belum</span>
                                        @endif
                                    </td>
                                    <td>{{ $bill->created_at->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center small">Belum ada riwayat tagihan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection