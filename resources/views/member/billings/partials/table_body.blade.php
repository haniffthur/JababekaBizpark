{{-- resources/views/member/billings/partials/table_body.blade.php --}}
@forelse ($billings as $billing)
    <tr>
        <td>#{{ $billing->id }}</td>
        <td>Rp {{ number_format($billing->total_amount, 0, ',', '.') }}</td>
        <td>
            @if ($billing->status == 'paid')
                <span class="badge badge-success">Lunas (Paid)</span>
            @elseif ($billing->status == 'pending_verification')
                <span class="badge badge-info">Menunggu Verifikasi</span>
            @else
                <span class="badge badge-warning">Belum Bayar</span>
            @endif
        </td>
        <td>
            @if ($billing->proof_image)
                <a href="{{ asset('storage/' . $billing->proof_image) }}" target="_blank" class="btn btn-sm btn-info"><i class="fas fa-image"></i> Lihat</a>
            @else
                <span class="text-muted small">-</span>
            @endif
        </td>
        <td>{{ $billing->due_date ? $billing->due_date->format('d/m/Y') : '-' }}</td>
        <td>
            @if ($billing->status != 'paid' && $billing->status != 'pending_verification')
                {{-- Tombol Bayar --}}
                <button type="button" class="btn btn-primary btn-sm" 
                        data-toggle="modal" data-target="#payModal" 
                        data-id="{{ $billing->id }}"
                        data-amount="{{ number_format($billing->total_amount, 0, ',', '.') }}">
                    <i class="fas fa-upload"></i> Bayar
                </button>
            @elseif ($billing->status == 'pending_verification')
                <button class="btn btn-secondary btn-sm" disabled>Sedang Diverifikasi</button>
            @else
                <button class="btn btn-success btn-sm" disabled>Lunas</button>
            @endif
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center">Anda belum memiliki tagihan.</td></tr>
@endforelse