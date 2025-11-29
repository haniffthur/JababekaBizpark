@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Member Baru</h1>
    <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Member Baru</h6>
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

        <form action="{{ route('admin.members.store') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" 
                       value="{{ old('name') }}" required>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" name="email" 
                       value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" name="password_confirmation" required>
            </div>

            
           <hr>
<h6 class="font-weight-bold text-primary">Input 4 QR Code Pribadi Member</h6>
<p class="text-muted small">Input plat nomor untuk 4 QR Code pribadi (reusable) milik member ini. Kosongkan jika tidak ada.</p>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="plate_1">Plat Nomor Pribadi 1</label>
            <input type="text" class="form-control @error('plate_1') is-invalid @enderror" 
                   id="plate_1" name="plate_1" value="{{ old('plate_1') }}">
            @error('plate_1')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="plate_2">Plat Nomor Pribadi 2</label>
            <input type="text" class="form-control @error('plate_2') is-invalid @enderror" 
                   id="plate_2" name="plate_2" value="{{ old('plate_2') }}">
            @error('plate_2')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="plate_3">Plat Nomor Pribadi 3</label>
            <input type="text" class="form-control @error('plate_3') is-invalid @enderror" 
                   id="plate_3" name="plate_3" value="{{ old('plate_3') }}">
            @error('plate_3')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="plate_4">Plat Nomor Pribadi 4</label>
            <input type="text" class="form-control @error('plate_4') is-invalid @enderror" 
                   id="plate_4" name="plate_4" value="{{ old('plate_4') }}">
            @error('plate_4')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<button type="submit" class="btn btn-primary">Simpan Member</button>

        </form>
    </div>
</div>

@endsection