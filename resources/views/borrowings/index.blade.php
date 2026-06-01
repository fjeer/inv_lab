@extends('layouts.app')
@section('title', 'Peminjaman Lab')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Peminjaman</h1>
        <p class="text-slate-500 text-sm mt-1">Status permohonan peminjaman laboratorium</p>
    </div>
    <a href="{{ route('borrowings.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Pinjam Lab
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <select id="filter-status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Status</option>
            @foreach(['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak','completed'=>'Selesai','cancelled'=>'Dibatalkan'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>@endforeach
        </select>
        <select id="filter-laboratory" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Laboratorium</option>
            @foreach($laboratories as $lab)
            <option value="{{ $lab->id }}">{{ $lab->name }}</option>@endforeach
        </select>
        <select id="filter-trash" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Data Aktif</option>
            <option value="with">Semua Data</option>
            <option value="only">Data Terhapus</option>
        </select>
        <button type="button" id="btn-filter" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-colors">Filter</button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="borrowings-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Peminjam</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Laboratorium</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Tanggal</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Waktu</th>
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
    const table = $('#borrowings-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/borrowings',
            data: function (d) {
                d.status = $('#filter-status').val();
                d.laboratory_id = $('#filter-laboratory').val();
                d.trash_status = $('#filter-trash').val();
            },
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { data: 'user.name', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'laboratory.name', className: 'px-5 py-4 text-slate-600' },
            {
                data: 'borrow_date',
                render: (data) => new Date(data).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
            },
            {
                data: null,
                render: (row) => `<span class="font-mono text-xs">${row.start_time.substring(0,5)} - ${row.end_time.substring(0,5)}</span>`
            },
            {
                data: 'status',
                render: (data) => {
                    const colors = {
                        pending: 'bg-amber-50 text-amber-600',
                        approved: 'bg-emerald-50 text-emerald-600',
                        rejected: 'bg-red-50 text-red-600',
                        completed: 'bg-blue-50 text-blue-600',
                        cancelled: 'bg-slate-100 text-slate-500'
                    };
                    return `<span class="text-[10px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider ${colors[data] || 'bg-slate-50'}">${data}</span>`;
                }
            },
            {
                data: 'id',
                className: 'px-5 py-4 text-right',
                render: (data, type, row) => {
                    let actions = `<a href="/borrowings/${data}" class="text-blue-600 hover:text-blue-700 font-medium mr-3">Detail</a>`;
                    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten'))
                        if(row.status === 'pending') {
                            actions += `<button onclick="approveBorrowing(${data})" class="text-emerald-600 hover:text-emerald-700 font-medium mr-3">Setujui</button>`;
                        }
                        actions += `<button onclick="deleteBorrowing(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>`;
                    @endif
                    return actions;
                }
            }
        ],
        order: [[2, 'desc']]
    });

    $('#btn-filter').click(function() {
        table.ajax.reload();
    });

    window.approveBorrowing = (id) => {
        Swal.fire({ title: 'Setujui Peminjaman?', icon: 'question', showCancelButton: true }).then(r => {
            if(r.isConfirmed) $.post(`/api/borrowings/${id}/approve`, () => {
                window.showAlert('Disetujui', 'Peminjaman telah disetujui');
                table.ajax.reload();
            });
        });
    };

    window.deleteBorrowing = (id) => {
        Swal.fire({
            title: 'Hapus Peminjaman?',
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
                    url: `/api/borrowings/${id}`,
                    type: 'DELETE',
                    success: (res) => {
                        window.showAlert('Berhasil', 'Peminjaman telah dihapus', 'success');
                        table.ajax.reload();
                    },
                    error: (err) => {
                        Swal.fire('Error', err.responseJSON?.message || 'Gagal menghapus peminjaman.', 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
@endsection
