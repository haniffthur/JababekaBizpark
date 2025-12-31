@extends('layouts.app')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Member Baru</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.members.store') }}" method="POST">
            @csrf
            
            {{-- DATA DIRI MEMBER --}}
            <h5 class="mb-3 text-gray-800 border-bottom pb-2">Data Diri</h5>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- INPUT 4 KENDARAAN PRIBADI (WAJIB) --}}
            <h5 class="mt-4 mb-3 text-gray-800 border-bottom pb-2">Data Kendaraan Pribadi (Wajib 4 Unit)</h5>
            
            <div class="row">
                @for ($i = 1; $i <= 4; $i++)
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Plat Nomor Kendaraan #{{ $i }} <span class="text-danger">*</span></label>
                        
                        {{-- PERUBAHAN PENTING DI SINI: name="plate_{{ $i }}" --}}
                        <input type="text" 
                               name="plate_{{ $i }}" 
                               class="form-control @error('plate_'.$i) is-invalid @enderror" 
                               placeholder="Contoh: B 1234 ABC"
                               value="{{ old('plate_'.$i) }}" 
                               required>
                        
                        {{-- Error Message juga disesuaikan --}}
                        @error('plate_'.$i) 
                            <div class="invalid-feedback">{{ $message }}</div> 
                        @enderror
                    </div>
                </div>
                @endfor
            </div>

            <small class="text-muted d-block mb-3">* Sistem akan otomatis membuatkan QR Code untuk ke-4 plat nomor di atas.</small>

            <button type="submit" class="btn btn-primary btn-block">Simpan Member & Generate QR</button>
        </form>
    </div>
</div>
@endsection