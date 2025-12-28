@extends('layouts.app')

@section('title', 'Manajemen Tagihan')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Manajemen Tagihan</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Tagihan</h6>
            
            {{-- Tombol Generate Tagihan --}}
            <button type="button" id="btn-generate" class="btn btn-primary btn-sm">
                <i class="fas fa-magic"></i> Generate Tagihan
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>ID</th>
                            <th>Member & Deskripsi</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Bukti</th>
                            <th>Jatuh Tempo</th>
                            <th style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    
                    {{-- DISINI KITA PANGGIL PARTIAL YANG KAMU KIRIM --}}
                    {{-- Berikan ID="table-content" agar bisa dipilih oleh jQuery --}}
                    <tbody id="table-content">
                        @include('admin.billings.partials.table_body')
                    </tbody>
                </table>
            </div>
            
            {{-- Wadah Pagination --}}
            <div id="pagination-links" class="mt-3">
                {{ $billings->links() }}
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document"> {{-- modal-lg agar lebar --}}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rincian Tagihan #<span id="det-id"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nama Member:</strong> <span id="det-name"></span><br>
                        <strong>Email:</strong> <span id="det-email"></span><br>
                        <strong>Status:</strong> <span id="det-status"></span>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <strong>Tanggal Tagihan:</strong> <span id="det-date"></span><br>
                        <strong>Jatuh Tempo:</strong> <span id="det-due"></span><br>
                        <h4 class="text-primary mt-2" id="det-total"></h4>
                    </div>
                </div>

                <hr>
                <h6>Rincian Biaya:</h6>
                {{-- Tabel kecil untuk list Daily Charges (Inap) --}}
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Keterangan</th>
                            <th>Tanggal</th>
                            <th class="text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody id="det-list">
                        </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- SCRIPT AJAX --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-detail', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let url = "/admin/billings/" + id; // Route show

            // Tampilkan Loading di Modal (Opsional, biar UX bagus)
            $('#det-list').html('<tr><td colspan="3" class="text-center">Loading...</td></tr>');
            $('#detailModal').modal('show');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(res) {
                    // Isi Header Modal
                    $('#det-id').text(res.id);
                    $('#det-name').text(res.user_name);
                    $('#det-email').text(res.user_email);
                    $('#det-date').text(res.created_at);
                    $('#det-due').text(res.due_date);
                    $('#det-total').text(res.total_formatted);

                    // Badge Status
                    let badgeClass = 'badge-secondary';
                    if(res.status === 'paid') badgeClass = 'badge-success';
                    else if(res.status === 'pending_verification') badgeClass = 'badge-info';
                    else if(res.status === 'unpaid') badgeClass = 'badge-warning';
                    else if(res.status === 'rejected') badgeClass = 'badge-danger';
                    
                    $('#det-status').html(`<span class="badge ${badgeClass}">${res.status.toUpperCase()}</span>`);

                    // Isi Tabel Rincian
                    let rows = '';
                    if(res.items.length > 0) {
                        res.items.forEach(item => {
                            rows += `
                                <tr>
                                    <td>${item.desc}</td>
                                    <td>${item.date}</td>
                                    <td class="text-right">${item.amount}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rows = '<tr><td colspan="3" class="text-center">Tidak ada rincian item.</td></tr>';
                    }
                    $('#det-list').html(rows);
                },
                error: function() {
                    alert('Gagal mengambil detail tagihan.');
                    $('#detailModal').modal('hide');
                }
            });
        });
        // Setup CSRF
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        // --- 1. FUNGSI LOAD TABLE (Digunakan untuk Refresh) ---
        function loadTable(url) {
            url = url || "{{ route('admin.billings.index') }}";
            
            // Kita tidak pakai efek opacity saat auto-refresh 
            // supaya admin tidak merasa terganggu kedip-kedip
            // $('#table-content').css('opacity', '0.5'); 

            $.get(url, function(data) {
                $('#table-content').html(data.html);
                $('#pagination-links').html(data.pagination);
                // $('#table-content').css('opacity', '1');
            });
        }

        // --- 2. AUTO REFRESH SETIAP 5 DETIK ---
        setInterval(function() {
            // Cek: Jangan refresh jika Admin sedang membuka Popup SweetAlert!
            // Agar Admin tidak kehilangan fokus saat mau Approve/Reject
            if (!Swal.isVisible()) {
                console.log('Auto refreshing data...'); // Debugging
                loadTable(); 
            }
        }, 5000); // 5000 ms = 5 Detik

        // --- 3. HANDLE TOMBOL ACTION & GENERATE (Sama seperti sebelumnya) ---
        
        $('#btn-generate').click(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Generate Tagihan?',
                showCancelButton: true,
                confirmButtonText: 'Ya'
            }).then((result) => {
                if(result.isConfirmed) {
                    Swal.fire({title: 'Loading...', didOpen: () => Swal.showLoading()});
                    $.post("{{ route('admin.billings.generate') }}", function(res) {
                        Swal.fire('Sukses', res.message, 'success');
                        loadTable(); 
                    });
                }
            });
        });

        $(document).on('click', '.btn-action', function(e) {
            e.preventDefault();
            let btn = $(this);
            let id = btn.data('id');
            let action = btn.data('action');
            let url, title, method = 'POST';

            if(action === 'approve') {
                url = "/admin/billings/" + id + "/approve"; title = 'Setujui?';
            } else if(action === 'reject') {
                url = "/admin/billings/" + id + "/reject"; title = 'Tolak?';
            } else if(action === 'delete') {
                url = "/admin/billings/" + id; title = 'Hapus?'; method = 'DELETE';
            }

            Swal.fire({
                title: title,
                showCancelButton: true,
                confirmButtonText: 'Ya'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({title: 'Memproses...', didOpen: () => Swal.showLoading()});
                    $.ajax({
                        url: url, type: method,
                        success: function(res) {
                            Swal.fire('Berhasil', res.message, 'success');
                            loadTable();
                        }
                    });
                }
            });
        });

        // Handle Pagination Click
        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            loadTable($(this).attr('href'));
        });

        

    });
</script>
@endpush