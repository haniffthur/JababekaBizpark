@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Member: {{ $member->name }}</h1>
    <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Edit Member</h6>
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

        <form action="{{ route('admin.members.update', $member->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" name="name" 
                       value="{{ old('name', $member->name) }}" required>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" name="email" 
                       value="{{ old('email', $member->email) }}" required>
            </div>
            
            {{-- =================================== --}}
            {{-- == TAMBAHKAN BLOK DROPDOWN INI == --}}
            {{-- =================================== --}}
            <div class="form-group">
                <label for="ipl_status">Status Pembayaran IPL</label>
                <select name="ipl_status" id="ipl_status" class="form-control @error('ipl_status') is-invalid @enderror" required>
                    <option value="unpaid" {{ old('ipl_status', $member->ipl_status) == 'unpaid' ? 'selected' : '' }}>
                        Belum Bayar
                    </option>
                    <option value="paid" {{ old('ipl_status', $member->ipl_status) == 'paid' ? 'selected' : '' }}>
                        Sudah Bayar
                    </option>
                </select>
                @error('ipl_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr>
            <p class="text-muted">Kosongkan password jika tidak ingin mengubahnya.</p>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" name="password">
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" name="password_confirmation">
            </div>
            
            <button type="submit" class="btn btn-primary">Update Member</button>

        </form>
    </div>
</div>

@endsection