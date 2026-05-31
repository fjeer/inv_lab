@extends('layouts.app')
@section('title', 'Pengadaan')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Pengajuan Pengadaan</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola permohonan pengadaan peralatan baru</p>
    </div>
    <a href="{{ route('procurements.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Buat Pengajuan
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="procurement-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">No. Pengajuan</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Judul</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Pengaju</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Prioritas</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Estimasi Biaya</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#procurement-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/procurements',
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { data: 'procurement_number', className: 'px-5 py-4 font-mono text-xs text-slate-500' },
            { data: 'title', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'requester.name', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'priority',
                render: function(data) {
                    const colors = { low: 'bg-slate-50 text-slate-600', medium: 'bg-blue-50 text-blue-600', high: 'bg-amber-50 text-amber-600', urgent: 'bg-red-50 text-red-600' };
                    return `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${colors[data] || 'bg-slate-50'}">${data}</span>`;
                }
            },
            { 
                data: 'total_estimated_cost',
                render: function(data) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(data);
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    const colors = {
                        draft: 'bg-slate-100 text-slate-600',
                        submitted: 'bg-blue-50 text-blue-600',
                        in_review: 'bg-amber-50 text-amber-600',
                        approved: 'bg-emerald-50 text-emerald-600',
                        rejected: 'bg-red-50 text-red-600',
                        ordered: 'bg-indigo-50 text-indigo-600',
                        received: 'bg-teal-50 text-teal-600',
                        closed: 'bg-slate-200 text-slate-700'
                    };
                    const labels = {
                        draft: 'Draft',
                        submitted: 'Diajukan',
                        in_review: 'Ditinjau',
                        approved: 'Disetujui',
                        rejected: 'Ditolak',
                        ordered: 'Dipesan',
                        received: 'Diterima',
                        closed: 'Selesai'
                    };
                    return `<span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase tracking-wider ${colors[data] || 'bg-slate-50'}">${labels[data] || data}</span>`;
                }
            },
            { 
                data: 'id', 
                className: 'px-5 py-4 text-right',
                render: function(data) {
                    let actions = `<a href="/procurements/${data}" class="text-blue-600 hover:text-blue-700 font-medium mr-3">Detail</a>`;
                    @if(Auth::user()->hasRole('admin_lab', 'admin'))
                        actions += `<button onclick="deleteProcurement(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>`;
                    @endif
                    return actions;
                }
            }
        ],
        order: [[0, 'desc']]
    });

    window.deleteProcurement = (id) => {
        Swal.fire({
            title: 'Hapus Pengajuan?',
            text: "Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(r => {
            if(r.isConfirmed) {
                $.ajax({
                    url: `/api/procurements/${id}`,
                    type: 'DELETE',
                    success: (res) => {
                        window.showAlert('Berhasil', 'Pengajuan pengadaan telah dihapus', 'success');
                        $('#procurement-table').DataTable().ajax.reload();
                    },
                    error: (err) => {
                        Swal.fire('Error', err.responseJSON?.message || 'Gagal menghapus pengajuan.', 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
@endsection
