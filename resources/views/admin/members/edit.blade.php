@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Member: {{ $member->name }}</h1>
    <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

@if (session('error'))
<div class="alert alert-danger shadow-sm">{{ session('error') }}</div>
@endif

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
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $member->name) }}" required>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $member->email) }}" required>
            </div>
            
            <div class="form-group">
                <label for="ipl_status">Status Pembayaran IPL</label>
                <select name="ipl_status" id="ipl_status" class="form-control" required>
                    <option value="unpaid" {{ old('ipl_status', $member->ipl_status) == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="paid" {{ old('ipl_status', $member->ipl_status) == 'paid' ? 'selected' : '' }}>Sudah Bayar</option>
                </select>
            </div>

            <hr>
            
            {{-- BAGIAN EDIT QR PRIBADI --}}
            <h6 class="font-weight-bold text-primary">Edit QR Code Pribadi (4 Slot)</h6>
            <p class="text-muted small">Ubah plat nomor di bawah ini untuk mengupdate data QR. Kosongkan jika ingin menghapus slot tersebut.</p>
            
            {{-- Ambil data QR yang sudah ada berdasarkan nama slot --}}
            @php
                $qr1 = $member->personalQrs->where('name', 'Pribadi 1')->first();
                $qr2 = $member->personalQrs->where('name', 'Pribadi 2')->first();
                $qr3 = $member->personalQrs->where('name', 'Pribadi 3')->first();
                $qr4 = $member->personalQrs->where('name', 'Pribadi 4')->first();
            @endphp

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plat Nomor Pribadi 1</label>
                        <input type="text" class="form-control" name="plate_1" 
                               value="{{ old('plate_1', $qr1 ? $qr1->license_plate : '') }}" placeholder="B 1234 XX">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plat Nomor Pribadi 2</label>
                        <input type="text" class="form-control" name="plate_2" 
                               value="{{ old('plate_2', $qr2 ? $qr2->license_plate : '') }}" placeholder="B 1234 XX">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plat Nomor Pribadi 3</label>
                        <input type="text" class="form-control" name="plate_3" 
                               value="{{ old('plate_3', $qr3 ? $qr3->license_plate : '') }}" placeholder="B 1234 XX">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Plat Nomor Pribadi 4</label>
                        <input type="text" class="form-control" name="plate_4" 
                               value="{{ old('plate_4', $qr4 ? $qr4->license_plate : '') }}" placeholder="B 1234 XX">
                    </div>
                </div>
            </div>

            <hr>
            <p class="text-muted small">Kosongkan password jika tidak ingin mengubahnya.</p>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block mt-3">Simpan Perubahan</button>

        </form>
    </div>
</div>

@endsection