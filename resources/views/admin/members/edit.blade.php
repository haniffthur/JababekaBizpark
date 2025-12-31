@extends('layouts.app')

@section('content')

{{-- ... Header dan Pesan Error (Sama) ... --}}

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Edit Member</h6>
    </div>
    <div class="card-body">
        
        <form action="{{ route('admin.members.update', $member->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- DATA DIRI (SAMA SEPERTI SEBELUMNYA) --}}
            <h5 class="mb-3 text-gray-800 border-bottom pb-2">Data Diri</h5>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control" name="name" value="{{ old('name', $member->name) }}" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" value="{{ old('email', $member->email) }}" required>
            </div>
            <div class="form-group">
                <label>Status IPL</label>
                <select name="ipl_status" class="form-control" required>
                    <option value="unpaid" {{ old('ipl_status', $member->ipl_status) == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="paid" {{ old('ipl_status', $member->ipl_status) == 'paid' ? 'selected' : '' }}>Sudah Bayar</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password Baru <small class="text-muted">(Kosongkan jika tidak ubah)</small></label>
                <input type="password" class="form-control" name="password">
            </div>

            <hr>

            {{-- 1. BAGIAN 4 QR UTAMA (STANDAR) DENGAN CHECKBOX RESET --}}
            <h5 class="mt-4 mb-3 text-gray-800 border-bottom pb-2">Data Kendaraan Pribadi (Wajib 4 Unit)</h5>
            
        @php
    $standardQrs = $standardQrs ?? collect();

    $getPlate = function ($name) use ($standardQrs) {
        $qr = $standardQrs->firstWhere('name', $name);
        return $qr->license_plate ?? '';
    };
@endphp


            <div class="row">
                @for ($i = 1; $i <= 4; $i++)
                <div class="col-md-6 mb-3">
                    <div class="card bg-light border-0 h-100">
                        <div class="card-body p-3">
                            <div class="form-group mb-1">
                                <label class="font-weight-bold">Plat Nomor Pribadi {{ $i }} <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('plate_'.$i) is-invalid @enderror" 
                                       name="plate_{{ $i }}" 
                                       value="{{ old('plate_'.$i, $getPlate('Pribadi '.$i)) }}" 
                                       required>
                                @error('plate_'.$i) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- CHECKBOX RESET QR --}}
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" 
                                       id="regen_std_{{ $i }}" 
                                       name="regen_standard[]" 
                                       value="{{ $i }}"> {{-- Value dikirim angka 1, 2, 3, atau 4 --}}
                                <label class="custom-control-label text-danger small" for="regen_std_{{ $i }}">
                                    <i class="fas fa-sync-alt"></i> Reset QR Code (Buat Baru)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                @endfor
            </div>

            {{-- 2. BAGIAN QR TAMBAHAN (DENGAN CHECKBOX RESET) --}}
            @if($extraQrs->count() > 0)
                <h5 class="mt-4 mb-3 text-info border-bottom pb-2">
                    <i class="fas fa-plus-circle"></i> Kendaraan Tambahan (Request Disetujui)
                </h5>
                <div class="alert alert-info small">
                    Centang "Hapus" untuk menghapus permanen, atau "Reset QR" untuk mengganti kode QR dengan yang baru.
                </div>

                <div class="row">
                    @foreach($extraQrs as $extra)
                    <div class="col-md-6 mb-3">
                        <div class="card border-left-info h-100 py-2 shadow-sm">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            {{ $extra->name }}
                                        </div>
                                        <div class="form-group mb-2">
                                            <input type="text" 
                                                   class="form-control form-control-sm font-weight-bold" 
                                                   name="extra_plates[{{ $extra->id }}]" 
                                                   value="{{ old('extra_plates.'.$extra->id, $extra->license_plate) }}" 
                                                   required>
                                        </div>

                                        {{-- CHECKBOX RESET QR TAMBAHAN --}}
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="regen_ext_{{ $extra->id }}" 
                                                   name="regen_extras[]" 
                                                   value="{{ $extra->id }}">
                                            <label class="custom-control-label text-warning small font-weight-bold" for="regen_ext_{{ $extra->id }}">
                                                <i class="fas fa-sync-alt"></i> Reset QR
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-auto pl-3 border-left">
                                        {{-- CHECKBOX HAPUS --}}
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                   id="del_{{ $extra->id }}" 
                                                   name="delete_extras[]" 
                                                   value="{{ $extra->id }}">
                                            <label class="custom-control-label text-danger font-weight-bold" for="del_{{ $extra->id }}">
                                                Hapus
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
            
            <button type="submit" class="btn btn-primary btn-block mt-4 py-2">
                <i class="fas fa-save"></i> Simpan Semua Perubahan
            </button>

        </form>
    </div>
</div>

@endsection