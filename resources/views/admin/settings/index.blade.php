@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Pengaturan Sistem</h1>
</div>

@if (session('success'))
<div class="alert alert-success shadow-sm">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger shadow-sm">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Manajemen Tarif & Pengaturan Umum</h6>
            </div>
            <div class="card-body">
               <form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    
    <div class="form-group row">
        <label for="overnight_rate" class="col-sm-4 col-form-label">
            Tarif Penginapan per Malam (Rp)
        </label>
        <div class="col-sm-8">
            <input type="number" 
                   class="form-control @error('overnight_rate') is-invalid @enderror" 
                   id="overnight_rate" name="overnight_rate" 
                   value="{{ old('overnight_rate', $overnightRate) }}" required>
            <small class="form-text text-muted">
                Tarif ini akan digunakan untuk menghitung tagihan truk yang menginap.
            </small>
        </div>
    </div>
    
    {{-- =================================== --}}
    {{-- == TAMBAHKAN BLOK BARU INI == --}}
    {{-- =================================== --}}
    <div class="form-group row">
        <label for="billing_integration_mode" class="col-sm-4 col-form-label">
            Mode Integrasi Billing
        </label>
        <div class="col-sm-8">
            <select name="billing_integration_mode" id="billing_integration_mode" class="form-control">
                <option value="local" {{ $billingMode == 'local' ? 'selected' : '' }}>
                    Lokal (Simpan di Database GudangJababeka)
                </option>
                <option value="ipl" {{ $billingMode == 'ipl' ? 'selected' : '' }}>
                    IPL (Kirim ke Sistem Eksternal)
                </option>
            </select>
            <small class="form-text text-muted">
                Pilih ke mana tagihan otomatis akan dikirim.
            </small>
        </div>
    </div>
    
    <hr>

    <div class="form-group row">
        <div class="col-sm-8 offset-sm-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Pengaturan
            </button>
        </div>
    </div>
</form>
            </div>
        </div>
    </div>
</div>

@endsection