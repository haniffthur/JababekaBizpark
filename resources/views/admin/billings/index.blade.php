@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tagihan Saya</h1>
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
                        <th>Bukti</th>
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
                            <td>
                                {{-- Tampilkan Bukti jika ada --}}
                                @if ($billing->proof_image)
                                    <a href="{{ asset('storage/' . $billing->proof_image) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>{{ $billing->due_date ? $billing->due_date->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if ($billing->status != 'paid')
                                    {{-- Tombol Bayar (Buka Modal) --}}
                                    <button type="button" class="btn btn-primary btn-sm" 
                                            data-toggle="modal" 
                                            data-target="#payModal" 
                                            data-id="{{ $billing->id }}"
                                            data-amount="{{ number_format($billing->total_amount, 0, ',', '.') }}">
                                        <i class="fas fa-upload"></i> Upload Bukti
                                    </button>
                                @else
                                    <button class="btn btn-secondary btn-sm" disabled>Sudah Lunas</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Anda belum memiliki tagihan.</td>
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
                    <p>ID Tagihan: <strong id="modalId"></strong></p>
                    <p>Nominal yang harus dibayar: <strong>Rp <span id="modalAmount"></span></strong></p>
                    
                    <div class="form-group">
                        <label>File Bukti Transfer (Gambar)</label>
                        <input type="file" name="proof_image" class="form-control-file" required accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, JPEG. Max: 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Bukti</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Script untuk mengisi data ke dalam Modal saat tombol diklik
    $('#payModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Tombol yang diklik
        var id = button.data('id'); 
        var amount = button.data('amount');

        var modal = $(this);
        modal.find('#modalId').text('#' + id);
        modal.find('#modalAmount').text(amount);
        
        // Update URL action pada form agar sesuai ID tagihan
        var actionUrl = "{{ url('member/billings') }}/" + id + "/pay";
        modal.find('#payForm').attr('action', actionUrl);
    });
</script>
@endpush