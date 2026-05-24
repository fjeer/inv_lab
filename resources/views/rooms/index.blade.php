@extends('layouts.app')
@section('title', 'Data Ruangan')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Ruangan</h1>
        <p class="text-slate-500 text-sm mt-1">Manajemen ruangan dalam gedung</p>
    </div>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Tambah Ruangan
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="rooms-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kode</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Nama Ruangan</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Gedung</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Lantai</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div id="room-modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modal-title" class="font-bold text-slate-800 text-lg">Tambah Ruangan</h3>
            <button onclick="closeModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">&times;</button>
        </div>
        <form id="room-form" class="p-6 space-y-4">
            <input type="hidden" name="id" id="room-id">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Gedung *</label>
                <select name="building_id" id="room-building" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                    <option value="">Pilih Gedung</option>
                    @foreach($buildings as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Ruangan *</label>
                <input type="text" name="name" id="room-name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Ruangan *</label>
                    <input type="text" name="code" id="room-code" required placeholder="R-XXX" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Lantai</label>
                    <input type="text" name="floor" id="room-floor" placeholder="1, 2, dst" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Keterangan</label>
                <textarea name="description" id="room-description" rows="2" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl shadow-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#rooms-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/rooms',
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { data: 'code', className: 'px-5 py-4 font-mono text-xs text-slate-500' },
            { data: 'name', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'building.name', className: 'px-5 py-4 text-slate-600' },
            { data: 'floor', className: 'px-5 py-4 text-slate-600', render: (data) => data || '-' },
            { 
                data: 'id', 
                className: 'px-5 py-4 text-right',
                render: (data) => `
                    <button onclick="editRoom(${data})" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</button>
                    <button onclick="deleteRoom(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                `
            }
        ]
    });

    $('#room-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#room-id').val();
        const data = $(this).serialize();
        $.ajax({
            url: id ? `/api/rooms/${id}` : '/api/rooms',
            type: id ? 'PUT' : 'POST',
            data: data,
            success: (res) => {
                Swal.fire('Berhasil!', res.message, 'success');
                closeModal();
                table.ajax.reload();
            },
            error: (err) => {
                const message = err.responseJSON?.message || 'Gagal memproses data.';
                Swal.fire('Error', message, 'error');
            }
        });
    });

    window.openModal = () => {
        $('#room-form')[0].reset();
        $('#room-id').val('');
        $('#modal-title').text('Tambah Ruangan');
        $('#room-modal').removeClass('hidden').addClass('flex');
    };

    window.closeModal = () => $('#room-modal').removeClass('flex').addClass('hidden');

    window.editRoom = (id) => {
        $.get(`/api/rooms/${id}`, (res) => {
            const r = res.data;
            $('#room-id').val(r.id);
            $('#room-building').val(r.building_id);
            $('#room-name').val(r.name);
            $('#room-code').val(r.code);
            $('#room-floor').val(r.floor);
            $('#room-description').val(r.description);
            $('#modal-title').text('Edit Ruangan');
            $('#room-modal').removeClass('hidden').addClass('flex');
        });
    };

    window.deleteRoom = (id) => {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data ruangan akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/rooms/${id}`,
                    type: 'DELETE',
                    success: (res) => {
                        Swal.fire('Terhapus!', res.message, 'success');
                        table.ajax.reload();
                    },
                    error: (err) => {
                        const message = err.responseJSON?.message || 'Gagal menghapus data.';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
@endsection
