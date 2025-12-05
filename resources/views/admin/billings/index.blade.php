@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manajemen Keuangan</h1>
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
                        <th>Bukti Bayar</th>
                        <th>Jatuh Tempo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($billings as $billing)
                        <tr>
                            <td>#{{ $billing->id }}</td>
                            <td>
                                <strong>{{ $billing->user->name ?? 'Member Dihapus' }}</strong>
                            </td>
                            <td>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if ($billing->status == 'paid')
                                    <span class="badge badge-success">Lunas</span>
                                @elseif ($billing->status == 'pending_verification')
                                    <span class="badge badge-info">Perlu Verifikasi</span>
                                @elseif ($billing->status == 'rejected')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-warning">Belum Bayar</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- PERBAIKAN: Gunakan $billing (bukan $bill) --}}
                                @if ($billing->proof_image)
                                    <a href="{{ asset('storage/' . $billing->proof_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-image"></i> Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>{{ $billing->due_date ? $billing->due_date->format('d/m/Y') : '-' }}</td>
                            <td style="min-width: 150px;">
                                
                                {{-- Tombol Aksi Berdasarkan Status --}}
                                @if($billing->status == 'pending_verification')
                                    {{-- Tombol Approve --}}
                                    <form action="{{ route('admin.billings.approve', $billing->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm" title="Setujui Pembayaran" onclick="return confirm('Setujui pembayaran ini?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    
                                    {{-- Tombol Reject --}}
                                    <form action="{{ route('admin.billings.reject', $billing->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-danger btn-sm" title="Tolak Bukti" onclick="return confirm('Tolak bukti pembayaran ini?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    {{-- Tombol Hapus (Hanya jika belum lunas atau reject, opsional) --}}
                                    <form action="{{ route('admin.billings.destroy', $billing->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus tagihan ini?')" title="Hapus Tagihan">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data tagihan.</td>
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