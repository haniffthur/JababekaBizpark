{{-- resources/views/admin/master_qrs/index.blade.php --}}
@extends('layouts.app') {{-- Sesuaikan dengan nama layout admin lu --}}

@section('content')
<div class="container-fluid">

    {{-- Page Heading --}}
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Master QR (Jalur VIP)</h1>
        <button href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tambah Master QR
        </button>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Data Table --}}
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kode Akses Master</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle" width="100%" cellspacing="0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th width="25%">Nama / Pemegang Akses</th>
                            <th class="text-center" width="15%">Visual QR</th>
                            <th width="20%">Kode Text</th>
                            <th class="text-center" width="10%">Status</th>
                            <th class="text-center" width="25%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($masterQrs as $qr)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td class="font-weight-bold">{{ $qr->name }}</td>
                            <td class="text-center">
                                {{-- Render QR Code mini langsung di tabel --}}
                                <div class="bg-white p-1 border rounded d-inline-block">
                                    {!! QrCode::size(60)->margin(1)->generate($qr->code) !!}
                                </div>
                            </td>
                            <td>
                                <code class="text-primary font-weight-bold" style="font-size: 1rem;">{{ $qr->code }}</code>
                            </td>
                            <td class="text-center">
                                @if($qr->is_active)
                                    <span class="badge badge-success px-2 py-1"><i class="fas fa-check-circle"></i> Aktif</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1"><i class="fas fa-ban"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                {{-- Tombol Download --}}
                                <a href="{{ route('admin.master-qrs.download', $qr->id) }}" class="btn btn-sm btn-info mb-1" title="Download QR (PNG)">
                                    <i class="fas fa-download"></i>
                                </a>

                                {{-- Tombol Edit --}}
                                <button class="btn btn-sm btn-warning mb-1" data-toggle="modal" data-target="#editModal{{ $qr->id }}" title="Edit Nama">
                                    <i class="fas fa-edit"></i>
                                </button>

                                {{-- Tombol Toggle Status --}}
                                <form action="{{ route('admin.master-qrs.toggle', $qr->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $qr->is_active ? 'btn-secondary' : 'btn-success' }} mb-1" title="{{ $qr->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="fas {{ $qr->is_active ? 'fa-times' : 'fa-check' }}"></i>
                                    </button>
                                </form>

                                {{-- Tombol Delete --}}
                                <form action="{{ route('admin.master-qrs.destroy', $qr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Master QR ini secara permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger mb-1" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        {{-- ===================================== --}}
                        {{-- MODAL EDIT (Di dalam loop per item)   --}}
                        {{-- ===================================== --}}
                        <div class="modal fade" id="editModal{{ $qr->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title font-weight-bold text-gray-800">Edit Master QR</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('admin.master-qrs.update', $qr->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="name" class="font-weight-bold">Nama / Pemegang Akses</label>
                                                <input type="text" name="name" class="form-control" value="{{ $qr->name }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label class="font-weight-bold">Kode QR (Readonly)</label>
                                                <input type="text" class="form-control" value="{{ $qr->code }}" readonly disabled>
                                                <small class="text-danger">*Kode akses tidak dapat diubah demi keamanan.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-3x mb-3 text-gray-300 d-block"></i>
                                Belum ada data Master QR. Klik tombol "Tambah Master QR" di atas untuk membuat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- MODAL TAMBAH (Di luar tabel/loop)     --}}

<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-gray-800">Tambah Master QR Baru</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.master-qrs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                        <i class="fas fa-info-circle"></i> Master QR ini memiliki hak bypass masuk/keluar gerbang tanpa validasi plat nomor.
                    </div>
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nama / Pemegang Akses <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Direktur Utama" required autofocus>
                    </div>
                    <div class="form-group text-center bg-light p-3 border rounded mt-3">
                        <i class="fas fa-qrcode fa-3x text-gray-400 mb-2"></i>
                        <p class="mb-0 text-muted" style="font-size: 0.85rem;">Gambar & Kode QR akan di-generate otomatis oleh sistem setelah Anda menyimpannya.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-magic"></i> Generate QR Code</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection