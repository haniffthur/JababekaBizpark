@extends('layouts.app')
@section('content')
<div class="card shadow mb-4" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">Tambah QR Pribadi untuk: {{ $member->name }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.members.personal-qrs.store', $member->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Label Kendaraan</label>
                <input type="text" name="name" class="form-control" required placeholder="Contoh: Mobil Dinas, Motor Anak">
            </div>
            <div class="form-group">
                <label>Plat Nomor</label>
                <input type="text" name="license_plate" class="form-control" required placeholder="B 1234 XXX">
            </div>
            <button type="submit" class="btn btn-info btn-block">Simpan & Generate QR Pribadi</button>
        </form>
    </div>
</div>
@endsection