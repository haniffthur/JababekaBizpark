@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tagihan IPL Bulanan</h1>
</div>

@if ($errors->any())
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif

@if (auth()->user()->ipl_status == 'unpaid')
    <div class="alert alert-danger border-left-danger shadow-sm">
        <strong>Akses Diblokir!</strong> Anda memiliki tagihan IPL yang belum lunas. QR Code Pribadi tidak dapat digunakan.
    </div>
@else
    <div class="alert alert-success border-left-success shadow-sm">
        <strong>Akses Aktif.</strong> Terimakasih telah membayar IPL.
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Riwayat Tagihan</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Bukti</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bills as $bill)
                        <tr>
                            <td><strong>{{ $bill->period }}</strong></td>
                            <td>Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td>
                                @if ($bill->status == 'paid')
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-danger">Belum Bayar</span>
                                @endif
                            </td>
                            <td>
                                @if ($bill->proof_image)
                                    <a href="{{ asset('storage/' . $bill->proof_image) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($bill->status == 'unpaid')
                                    {{-- Tombol Membuka Modal --}}
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            data-toggle="modal" 
                                            data-target="#payModal" 
                                            data-id="{{ $bill->id }}"
                                            data-period="{{ $bill->period }}"
                                            data-amount="{{ number_format($bill->amount, 0, ',', '.') }}">
                                        <i class="fas fa-upload"></i> Upload Bukti
                                    </button>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>Sudah Lunas</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Belum ada tagihan IPL.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $bills->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

{{-- MODAL UPLOAD BUKTI --}}
<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST" enctype="multipart/form-data" id="payForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Bukti Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Tagihan Periode: <strong id="modalPeriod"></strong></p>
                    <p>Nominal: <strong>Rp <span id="modalAmount"></span></strong></p>
                    
                    <div class="form-group">
                        <label>File Bukti Transfer (Gambar)</label>
                        <input type="file" name="proof_image" class="form-control-file" required accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, JPEG. Max: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Bukti & Bayar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Script untuk memindahkan data ke dalam Modal
    $('#payModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); 
        var id = button.data('id'); 
        var period = button.data('period');
        var amount = button.data('amount');

        var modal = $(this);
        modal.find('#modalPeriod').text(period);
        modal.find('#modalAmount').text(amount);
        
        // Update action URL form
        var actionUrl = "{{ url('member/ipl-bills') }}/" + id + "/pay";
        modal.find('#payForm').attr('action', actionUrl);
    });
</script>
@endpush