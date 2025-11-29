@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Mesin: {{ $gateMachine->termno }}</h1>
    <a href="{{ route('admin.gate-machines.index') }}" class="btn btn-sm btn-secondary shadow-sm">Kembali</a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('admin.gate-machines.update', $gateMachine->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Terminal No (ID Unik)</label>
                <input type="text" name="termno" class="form-control @error('termno') is-invalid @enderror" value="{{ old('termno', $gateMachine->termno) }}">
                <small class="text-muted">Gunakan angka <strong>Ganjil</strong> untuk MASUK, dan <strong>Genap</strong> untuk KELUAR.</small>
                @error('termno')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $gateMachine->location) }}">
                @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
@endsection