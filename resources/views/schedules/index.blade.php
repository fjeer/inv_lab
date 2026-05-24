@extends('layouts.app')
@section('title', 'Jadwal Lab')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Jadwal Penggunaan Lab</h1>
        <p class="text-slate-500 text-sm mt-1">Jadwal praktikum dan perkuliahan reguler</p>
    </div>
    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab'))
    <a href="{{ route('schedules.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Tambah Jadwal
    </a>
    @endif
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <select id="filter-laboratory" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Laboratorium</option>
            @foreach($laboratories as $lab)
            <option value="{{ $lab->id }}">{{ $lab->name }}</option>
            @endforeach
        </select>
        <select id="filter-day" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Hari</option>
            <option value="monday">Senin</option>
            <option value="tuesday">Selasa</option>
            <option value="wednesday">Rabu</option>
            <option value="thursday">Kamis</option>
            <option value="friday">Jumat</option>
            <option value="saturday">Sabtu</option>
            <option value="sunday">Minggu</option>
        </select>
        <button type="button" id="btn-filter" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-colors">Filter</button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="schedules-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Hari</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Jam</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kegiatan</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Laboratorium</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kelas / Semester</th>
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
    const table = $('#schedules-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/schedules',
            data: function (d) {
                d.laboratory_id = $('#filter-laboratory').val();
                d.day = $('#filter-day').val();
            },
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { 
                data: 'day_of_week', 
                className: 'px-5 py-4 font-bold text-slate-700 capitalize',
                render: (data) => {
                    const days = { monday: 'Senin', tuesday: 'Selasa', wednesday: 'Rabu', thursday: 'Kamis', friday: 'Jumat', saturday: 'Sabtu', sunday: 'Minggu' };
                    return days[data] || data;
                }
            },
            { 
                data: null, 
                className: 'px-5 py-4 font-mono text-xs',
                render: (row) => `${row.start_time.substring(0,5)} - ${row.end_time.substring(0,5)}`
            },
            { data: 'title', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'laboratory.name', className: 'px-5 py-4 text-slate-600' },
            { data: null, render: (row) => `${row.class_group} / ${row.semester}` },
            { 
                data: 'id', 
                className: 'px-5 py-4 text-right',
                render: (data) => {
                    let actions = `<a href="/schedules/${data}" class="text-blue-600 hover:text-blue-700 font-medium mr-3">Detail</a>`;
                    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab'))
                        actions += `<a href="/schedules/${data}/edit" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</a>`;
                        actions += `<button onclick="deleteSchedule(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>`;
                    @endif
                    return actions;
                }
            }
        ],
        order: [[0, 'asc'], [1, 'asc']]
    });

    $('#btn-filter').click(function() {
        table.ajax.reload();
    });

    window.deleteSchedule = (id) => {
        Swal.fire({
            title: 'Hapus Jadwal?',
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
                    url: `/api/schedules/${id}`,
                    type: 'DELETE',
                    success: (res) => {
                        window.showAlert('Berhasil', 'Jadwal telah dihapus', 'success');
                        $('#schedules-table').DataTable().ajax.reload();
                    },
                    error: (err) => {
                        Swal.fire('Error', err.responseJSON?.message || 'Gagal menghapus jadwal.', 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
@endsection
