@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tambah Truk untuk: {{ $member->name }}</h6>
            </div>
            <div class="card-body">
                
                {{-- Form mengarah ke method storeForMember --}}
                <form action="{{ route('admin.members.trucks.store', $member->id) }}" method="POST">
                    @csrf
                    
                    {{-- Input Plat Nomor --}}
                    <div class="form-group">
                        <label class="font-weight-bold">Plat Nomor Truk <span class="text-danger">*</span></label>
                        <input type="text" 
                               name="license_plate" 
                               class="form-control @error('license_plate') is-invalid @enderror" 
                               placeholder="Contoh: B 9000 XYZ"
                               value="{{ old('license_plate') }}"
                               required>
                        @error('license_plate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Input Nama Supir --}}
                    <div class="form-group">
                        <label>Nama Supir (Opsional)</label>
                        <input type="text" 
                               name="driver_name" 
                               class="form-control" 
                               placeholder="Nama Supir Utama"
                               value="{{ old('driver_name') }}">
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan & Generate QR
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection