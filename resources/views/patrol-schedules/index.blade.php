@extends('layouts.app')
@section('title', 'Jadwal Patroli Lab')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Jadwal Patroli Lab</h1>
        <p class="text-slate-500 text-sm mt-1">Jadwal pemeriksaan lab rutin oleh Asisten</p>
    </div>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Tambah Jadwal
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    {{-- Custom Filters --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Laboratorium</label>
            <select id="filter-lab" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <option value="">Semua Lab</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Hari</label>
            <select id="filter-day" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <option value="">Semua Hari</option>
                <option value="monday">Senin</option>
                <option value="tuesday">Selasa</option>
                <option value="wednesday">Rabu</option>
                <option value="thursday">Kamis</option>
                <option value="friday">Jumat</option>
                <option value="saturday">Sabtu</option>
                <option value="sunday">Minggu</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Asisten</label>
            <select id="filter-assistant" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <option value="">Semua Asisten</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Status Data</label>
            <select id="filter-trash" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                <option value="">Aktif</option>
                <option value="with">Semua</option>
                <option value="only">Terhapus</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="patrol-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Asisten</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Laboratorium</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Hari</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Waktu</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

{{-- Patrol Modal --}}
<div id="patrol-modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modal-title" class="font-bold text-slate-800 text-lg">Tambah Jadwal Patroli</h3>
            <button onclick="closeModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">&times;</button>
        </div>
        <form id="patrol-form" class="p-6 space-y-4">
            <input type="hidden" name="id" id="patrol-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Asisten *</label>
                    <select name="user_id" id="patrol-user" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Pilih Asisten</option>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Laboratorium *</label>
                    <select name="laboratory_id" id="patrol-lab" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Pilih Laboratorium</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Hari *</label>
                    <select name="day_of_week" id="patrol-day" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Pilih Hari</option>
                        <option value="monday">Senin</option>
                        <option value="tuesday">Selasa</option>
                        <option value="wednesday">Rabu</option>
                        <option value="thursday">Kamis</option>
                        <option value="friday">Jumat</option>
                        <option value="saturday">Sabtu</option>
                        <option value="sunday">Minggu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                    <select name="status" id="patrol-status" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Mulai *</label>
                    <input type="time" name="start_time" id="patrol-start" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Selesai *</label>
                    <input type="time" name="end_time" id="patrol-end" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
                    <textarea name="notes" id="patrol-notes" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="Opsional..."></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 shadow-lg shadow-blue-500/20 rounded-xl hover:bg-blue-700 transition-all">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Load options for selects
    $.get('/api/options', function(res) {
        if (res.status === 'success') {
            const labs = res.data.laboratories;
            const assistants = res.data.assistants || res.data.users || [];
            
            labs.forEach(l => {
                $('#patrol-lab').append(`<option value="${l.id}">${l.name}</option>`);
                $('#filter-lab').append(`<option value="${l.id}">${l.name}</option>`);
            });

            assistants.forEach(a => {
                $('#patrol-user').append(`<option value="${a.id}">${a.name}</option>`);
                $('#filter-assistant').append(`<option value="${a.id}">${a.name}</option>`);
            });
        }
    });

    const table = $('#patrol-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/patrol-schedules',
            data: function (d) {
                d.laboratory_id = $('#filter-lab').val();
                d.day_of_week = $('#filter-day').val();
                d.user_id = $('#filter-assistant').val();
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
                data: 'day_of_week', 
                render: (data) => {
                    const days = {
                        monday: 'Senin',
                        tuesday: 'Selasa',
                        wednesday: 'Rabu',
                        thursday: 'Kamis',
                        friday: 'Jumat',
                        saturday: 'Sabtu',
                        sunday: 'Minggu'
                    };
                    return days[data] || data;
                }
            },
            { 
                data: null, 
                render: (row) => {
                    const start = row.start_time.substring(0, 5);
                    const end = row.end_time.substring(0, 5);
                    return `<span class="font-mono text-xs text-slate-500">${start} - ${end}</span>`;
                }
            },
            { 
                data: 'status', 
                render: (data) => `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${data === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'}">${data === 'active' ? 'Aktif' : 'Nonaktif'}</span>`
            },
            { 
                data: 'id', 
                className: 'px-5 py-4 text-right whitespace-nowrap',
                render: (data, type, row) => `
                    <button onclick="editPatrol(${data})" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</button>
                    <button onclick="deletePatrol(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                `
            }
        ]
    });

    // Custom filtering
    $('#filter-lab, #filter-day, #filter-assistant, #filter-trash').on('change', function() {
        table.ajax.reload();
    });

    // Handle Form Submit
    $('#patrol-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#patrol-id').val();
        const url = id ? `/api/patrol-schedules/${id}` : '/api/patrol-schedules';
        const type = id ? 'PUT' : 'POST';
        
        const data = {
            user_id: $('#patrol-user').val(),
            laboratory_id: $('#patrol-lab').val(),
            day_of_week: $('#patrol-day').val(),
            start_time: $('#patrol-start').val(),
            end_time: $('#patrol-end').val(),
            status: $('#patrol-status').val(),
            notes: $('#patrol-notes').val(),
        };

        $.ajax({
            url: url,
            type: type,
            data: data,
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                closeModal();
                table.ajax.reload();
            },
            error: function(err) {
                const msg = err.responseJSON ? err.responseJSON.message : 'Terjadi kesalahan sistem';
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: msg
                });
            }
        });
    });
});

function openModal() {
    $('#modal-title').text('Tambah Jadwal Patroli');
    $('#patrol-form')[0].reset();
    $('#patrol-id').val('');
    $('#patrol-modal').removeClass('hidden');
}

function closeModal() {
    $('#patrol-modal').addClass('hidden');
}

function editPatrol(id) {
    $.get(`/api/patrol-schedules/${id}`, function(res) {
        if (res.status === 'success') {
            const data = res.data;
            $('#modal-title').text('Edit Jadwal Patroli');
            $('#patrol-id').val(data.id);
            $('#patrol-user').val(data.user_id);
            $('#patrol-lab').val(data.laboratory_id);
            $('#patrol-day').val(data.day_of_week);
            
            // Format time strings (H:i)
            $('#patrol-start').val(data.start_time.substring(0, 5));
            $('#patrol-end').val(data.end_time.substring(0, 5));
            
            $('#patrol-status').val(data.status);
            $('#patrol-notes').val(data.notes || '');
            $('#patrol-modal').removeClass('hidden');
        }
    });
}

function deletePatrol(id) {
    Swal.fire({
        title: 'Hapus Jadwal?',
        text: "Tindakan ini tidak dapat dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Ya, Hapus'
    }).then((r) => {
        if (r.isConfirmed) {
            $.ajax({
                url: `/api/patrol-schedules/${id}`,
                type: 'DELETE',
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Dihapus!',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    $('#patrol-table').DataTable().ajax.reload();
                }
            });
        }
    });
}
</script>
@endpush
@endsection
