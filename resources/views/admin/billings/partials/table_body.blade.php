{{-- resources/views/admin/billings/partials/table_body.blade.php --}}
@forelse ($billings as $billing)
    <tr>
        <td>#{{ $billing->id }}</td>
        <td><strong>{{ $billing->user->name ?? 'Member Dihapus' }}</strong></td>
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
        <td>{{ $billing->due_date ? $billing->due_date->format('d/m/Y') : '-' }}</td>
        <td style="min-width: 150px;">
            @if($billing->status == 'pending_verification')
                <form action="{{ route('admin.billings.approve', $billing->id) }}" method="POST" class="d-inline">
                    @csrf <button class="btn btn-success btn-sm" title="Setujui" onclick="return confirm('Setujui?')"><i class="fas fa-check"></i></button>
                </form>
                <form action="{{ route('admin.billings.reject', $billing->id) }}" method="POST" class="d-inline">
                    @csrf <button class="btn btn-danger btn-sm" title="Tolak" onclick="return confirm('Tolak?')"><i class="fas fa-times"></i></button>
                </form>
            @else
                <form action="{{ route('admin.billings.destroy', $billing->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus?')" title="Hapus"><i class="fas fa-trash"></i></button>
                </form>
            @endif
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center">Belum ada data tagihan.</td></tr>
@endforelse