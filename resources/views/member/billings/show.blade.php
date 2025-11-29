@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Detail Tagihan #{{ $billing->id }}</h1>
    <a href="{{ route('member.billings.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar Tagihan
    </a>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Invoice #{{ $billing->id }}</h6>
                @if ($billing->status == 'paid')
                    <span class="badge badge-success" style="font-size: 1rem;">LUNAS</span>
                @else
                    <span class="badge badge-warning" style="font-size: 1rem;">PENDING</span>
                @endif
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Ditagihkan Kepada:</h4>
                        <address>
                            <strong>{{ $billing->user->name }}</strong><br>
                            {{ $billing->user->email }}
                        </address>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <h4>Detail Tagihan:</h4>
                        <p>
                            <strong>Tgl. Tagihan:</strong> {{ $billing->created_at->format('d/m/Y') }}<br>
                            <strong>Jatuh Tempo:</strong> {{ $billing->due_date->format('d/m/Y') }}<br>
                        </p>
                    </div>
                </div>
                
                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th class="text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Tagihan (misal: biaya menginap, dll)</td>
                                        <td class="text-right">Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-right">Total:</th>
                                        <th class="text-right h4">Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection