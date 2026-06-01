@extends('layouts.app')
@section('title', 'Kategori Alat')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kategori Alat</h1>
        <p class="text-slate-500 text-sm mt-1">Manajemen kategori peralatan laboratorium</p>
    </div>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl">
        Tambah Kategori
    </button>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
        <label for="filter-trash" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Status Data</label>
        <select id="filter-trash" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm text-slate-600">
            <option value="">Aktif</option>
            <option value="with">Semua</option>
            <option value="only">Terhapus</option>
        </select>
    </div>
    <table id="category-table" class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50/50">
                <th class="text-left px-5 py-3.5">Nama Kategori</th>
                <th class="text-left px-5 py-3.5">Slug</th>
                <th class="text-left px-5 py-3.5">Deskripsi</th>
                <th class="text-right px-5 py-3.5">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<div id="cat-modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden animate-in fade-in zoom-in duration-200">
        <form id="cat-form" class="p-6 space-y-4">
            <h3 id="modal-title" class="font-bold text-slate-800 text-lg">Tambah Kategori</h3>
            <input type="hidden" id="cat-id">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Kategori *</label>
                <input type="text" name="name" id="cat-name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
                <textarea name="description" id="cat-desc" rows="3" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm font-medium text-slate-600">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#category-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/categories',
            data: function (d) {
                d.trash_status = $('#filter-trash').val();
            },
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { data: 'name', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'slug', className: 'px-5 py-4 text-slate-500 font-mono text-xs' },
            { data: 'description', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'id', className: 'px-5 py-4 text-right',
                render: (data) => `
                    <button onclick="editCat(${data})" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</button>
                    <button onclick="deleteCat(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                `
            }
        ]
    });

    $('#filter-trash').on('change', function() {
        table.ajax.reload();
    });

    $('#cat-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#cat-id').val();
        $.ajax({
            url: id ? `/api/categories/${id}` : '/api/categories',
            type: id ? 'PUT' : 'POST',
            data: $(this).serialize(),
            success: (res) => {
                window.showAlert('Success', res.message);
                closeModal();
                table.ajax.reload();
            }
        });
    });

    window.openModal = () => { $('#cat-form')[0].reset(); $('#cat-id').val(''); $('#cat-modal').removeClass('hidden').addClass('flex'); };
    window.closeModal = () => { $('#cat-modal').removeClass('flex').addClass('hidden'); };
    window.editCat = (id) => {
        $.get(`/api/categories/${id}`, (res) => {
            const c = res.data;
            $('#cat-id').val(c.id);
            $('#cat-name').val(c.name);
            $('#cat-desc').val(c.description);
            $('#cat-modal').removeClass('hidden').addClass('flex');
        });
    };
    window.deleteCat = (id) => {
        Swal.fire({ title: 'Hapus?', icon: 'warning', showCancelButton: true }).then(r => {
            if(r.isConfirmed) $.ajax({ url: `/api/categories/${id}`, type: 'DELETE', success: () => table.ajax.reload() });
        });
    };
});
</script>
@endpush
@endsection
