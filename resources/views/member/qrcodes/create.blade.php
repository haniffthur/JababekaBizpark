@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Generate QR Code Baru</h1>
    <a href="{{ route('member.qrcodes.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Pilih Truk</h6>
    </div>
    <div class="card-body">
        
        @if ($trucks->isEmpty())
            <div class="alert alert-warning">
                Anda harus <a href="{{ route('member.trucks.create') }}">mendaftarkan truk</a> terlebih dahulu sebelum bisa men-generate QR Code.
            </div>
        @else
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('member.qrcodes.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="truck_id">Pilih Truk</label>
                    <select name="truck_id" id="truck_id" class="form-control @error('truck_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Truk Anda --</option>
                        @foreach ($trucks as $truck)
                            <option value="{{ $truck->id }}" {{ old('truck_id') == $truck->id ? 'selected' : '' }}>
                                {{ $truck->license_plate }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">QR Code akan dibuat untuk truk yang Anda pilih.</small>
                </div>
                
                <button type="submit" class="btn btn-primary">Generate QR Code</button>
            </form>
        @endif
    </div>
</div>

@endsection