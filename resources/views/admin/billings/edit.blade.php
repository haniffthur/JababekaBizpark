@extends('layouts.app')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Tagihan #{{ $billing->id }}</h1>
    <a href="{{ route('admin.billings.index') }}" class="btn btn-sm btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Formulir Edit Tagihan</h6>
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

        <form action="{{ route('admin.billings.update', $billing->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label for="user_id">Pilih Member</label>
                <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                    <option value="">-- Pilih Member --</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" {{ old('user_id', $billing->user_id) == $member->id ? 'selected' : '' }}>
                            {{ $member->name }} ({{ $member->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="total_amount">Jumlah Tagihan (Rp)</label>
                <input type="number" 
                       class="form-control @error('total_amount') is-invalid @enderror" 
                       id="total_amount" name="total_amount" 
                       value="{{ old('total_amount', $billing->total_amount) }}" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                    <option value="pending" {{ old('status', $billing->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ old('status', $billing->status) == 'paid' ? 'selected' : '' }}>Lunas (Paid)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Jatuh Tempo</label>
                <input type="date" 
                       class="form-control @error('due_date') is-invalid @enderror" 
                       id="due_date" name="due_date" 
                       value="{{ old('due_date', $billing->due_date->format('Y-m-d')) }}" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Tagihan</button>
        </form>
    </div>
</div>

@endsection