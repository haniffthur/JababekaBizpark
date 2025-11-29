@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Truk: {{ $truck->license_plate }}</h1>
    <a href="{{ route('member.trucks.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Edit Truk</h6>
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

        <form action="{{ route('member.trucks.update', $truck->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Method Spoofing untuk UPDATE --}}
            
            <div class="form-group">
                <label for="license_plate">Plat Nomor</label>
                <input type="text" 
                       class="form-control @error('license_plate') is-invalid @enderror" 
                       id="license_plate" name="license_plate" 
                       value="{{ old('license_plate', $truck->license_plate) }}" 
                       placeholder="Contoh: B 1234 XYZ" required>
            </div>
            
            <div class="form-group">
                <label for="driver_name">Nama Supir (Opsional)</label>
                <input type="text" 
                       class="form-control @error('driver_name') is-invalid @enderror" 
                       id="driver_name" name="driver_name" 
                       value="{{ old('driver_name', $truck->driver_name) }}">
            </div>
            
            <button type="submit" class="btn btn-primary">Update Truk</button>

        </form>
    </div>
</div>

@endsection