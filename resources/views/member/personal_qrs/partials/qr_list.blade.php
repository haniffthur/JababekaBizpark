{{-- resources/views/member/personal_qrs/partials/qr_list.blade.php --}}

@forelse ($personalQrs as $qr)
    <div class="col-xl-6 col-md-12 mb-4" id="qr-card-{{ $qr->id }}">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">{{ $qr->name }} - {{ $qr->license_plate }}</h6>
            </div>
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    
                    <div class="col-md-6 text-center mb-3 mb-md-0">
                        @QrCode($qr->code)
                        <!-- <code class="d-block mt-2" style="font-size: 0.8rem; color: #e74a3b; word-break: break-all;">{{ $qr->code }}</code> -->
                    </div>

                    <div class="col-md-6">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Plat Nomor:</span>
                                <strong>{{ $qr->license_plate }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>Status:</span>
                                @if ($qr->status == 'aktif')
                                    <span class="badge badge-success">Di Dalam</span>
                                @else
                                    <span class="badge badge-secondary">Di Luar</span>
                                @endif
                            </li>
                            <li class="list-group-item px-0">
                                <small class="text-muted">
                                    Dibuat: {{ $qr->created_at->format('d/m/Y') }}
                                </small>
                            </li>
                            
                            <li class="list-group-item text-center px-0 pt-3">
                                {{-- Gunakan d-grid untuk membuat tombol full-width dan gap-2 untuk jarak --}}
                                <div class="d-grid gap-2">
                                    <a href="{{ route('member.personal_qrs.print', $qr->id) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-print fa-sm"></i> Cetak
                                    </a>
                                    <a href="{{ route('member.personal_qrs.download', $qr->id) }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-download fa-sm"></i> Download PDF
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <p>Admin belum mendaftarkan QR Code Pribadi untuk akun Anda.</p>
            </div>
        </div>
    </div>
@endforelse