@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit QR Pribadi: {{ $personalQr->name }}</h1>
    <a href="{{ route('admin.personal-qrs.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Edit QR Pribadi</h6>
    </div>
    <div class="card-body">
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.personal-qrs.update', $personalQr->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Method Spoofing untuk UPDATE --}}
            
            <div class="form-group">
                <label for="name">Nama Slot (Label)</label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" 
                       value="{{ old('name', $personalQr->name) }}" required>
            </div>
            
            <div class="form-group">
                <label for="license_plate">Plat Nomor Terikat</label>
                <input type="text" 
                       class="form-control @error('license_plate') is-invalid @enderror" 
                       id="license_plate" name="license_plate" 
                       value="{{ old('license_plate', $personalQr->license_plate) }}" required>
            </div>

            <hr>
            
            <div class="form-group">
                <label>Kode QR Saat Ini</label>
                <input type="text" class="form-control" value="{{ $personalQr->code }}" readonly>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="regenerate_code" id="regenerate_code">
                <label class="form-check-label" for="regenerate_code">
                    Regenerasi Kode QR? (Jika dicentang, kode lama akan hangus)
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Update QR Pribadi</button>

        </form>
    </div>
</div>

@endsection