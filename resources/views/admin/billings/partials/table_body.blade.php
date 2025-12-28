@forelse ($billings as $billing)
    <tr>
        <td>#{{ $billing->id }}</td>
        <td>
            <strong>{{ $billing->user->name ?? 'Member Dihapus' }}</strong>
            <br><small class="text-muted">{{ $billing->description }}</small>
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
            @if ($billing->proof_image)
                <a href="{{ asset('storage/' . $billing->proof_image) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-image"></i> Lihat
                </a>
            @else
                <span class="text-muted small">-</span>
            @endif
        </td>
        <td>{{ $billing->due_date ? \Carbon\Carbon::parse($billing->due_date)->format('d/m/Y') : '-' }}</td>
        <td style="min-width: 150px;">
            @if($billing->status == 'pending_verification')
                <button class="btn btn-success btn-sm btn-action" data-id="{{ $billing->id }}" data-action="approve" title="Setujui">
                    <i class="fas fa-check"></i>
                </button>
                <button class="btn btn-danger btn-sm btn-action" data-id="{{ $billing->id }}" data-action="reject" title="Tolak">
                    <i class="fas fa-times"></i>
                </button>
            @endif
            
            {{-- TOMBOL DETAIL (Pengganti Delete) --}}
            {{-- Kita beri class khusus 'btn-detail' agar beda logic dengan 'btn-action' --}}
            <button class="btn btn-sm btn-info btn-detail" data-id="{{ $billing->id }}" title="Lihat Rincian">
                <i class="fas fa-eye"></i>
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center">Belum ada data tagihan.</td></tr>
@endforelse