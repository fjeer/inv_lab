@extends('layouts.app')
@section('title', 'Kelola Pengguna')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kelola Pengguna</h1>
        <p class="text-slate-500 text-sm mt-1">Manajemen akun pengguna sistem</p>
    </div>
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Tambah Pengguna
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
    <table id="users-table" class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50/50 text-slate-600 font-semibold">
                <th class="px-5 py-3.5 text-left">Nama</th>
                <th class="px-5 py-3.5 text-left">Email</th>
                <th class="px-5 py-3.5 text-left">Role</th>
                <th class="px-5 py-3.5 text-left">Status</th>
                <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

{{-- User Modal --}}
<div id="user-modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modal-title" class="font-bold text-slate-800 text-lg">Tambah Pengguna</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form id="user-form" class="p-6 space-y-4">
            <input type="hidden" name="id" id="u-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="u-name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="u-email" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Role</label>
                    <select name="role" id="u-role" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none">
                        <option value="pengguna">Pengguna</option>
                        <option value="asisten_lab">Asisten Lab</option>
                        <option value="admin_lab">Admin Lab</option>
                    </select>
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">NIM / NIP</label>
                    <input type="text" name="nim_nip" id="u-nim" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Password</label>
                    <input type="password" name="password" id="u-pass" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none" placeholder="Isi untuk ubah">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-xl shadow-lg">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#users-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/users',
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
            { 
                data: 'name', className: 'px-5 py-4 font-medium text-slate-700',
                render: (data, t, row) => {
                    const avatarHtml = row.avatar 
                        ? `<img src="/storage/${row.avatar}" class="w-8 h-8 rounded-full object-cover border border-slate-100 shadow-sm">`
                        : `<div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold">${data[0].toUpperCase()}</div>`;
                    return `
                        <div class="flex items-center gap-3">
                            ${avatarHtml}
                            <span>${data}</span>
                        </div>
                    `;
                }
            },
            { data: 'email', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'role', className: 'px-5 py-4',
                render: (data) => {
                    const colors = { admin_lab: 'bg-purple-50 text-purple-600', asisten_lab: 'bg-blue-50 text-blue-600', pengguna: 'bg-slate-50 text-slate-600' };
                    return `<span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-lg ${colors[data] || 'bg-slate-50'}">${data.replace('_', ' ')}</span>`;
                }
            },
            { 
                data: 'is_active', className: 'px-5 py-4',
                render: (data) => `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${data ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'}">${data ? 'Aktif' : 'Nonaktif'}</span>`
            },
            { 
                data: 'id', className: 'px-5 py-4 text-right',
                render: (data) => `
                    <button onclick="editUser(${data})" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</button>
                    <button onclick="deleteUser(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                `
            }
        ]
    });

    $('#filter-trash').on('change', function() {
        table.ajax.reload();
    });

    $('#user-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#u-id').val();
        $.ajax({
            url: id ? `/api/users/${id}` : '/api/users',
            type: id ? 'PUT' : 'POST',
            data: $(this).serialize(),
            success: (res) => {
                window.showAlert('Berhasil!', res.message);
                closeModal();
                table.ajax.reload();
            },
            error: (err) => {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Error occuried';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    window.openModal = () => { $('#user-form')[0].reset(); $('#u-id').val(''); $('#modal-title').text('Tambah Pengguna'); $('#user-modal').removeClass('hidden').addClass('flex'); };
    window.closeModal = () => $('#user-modal').removeClass('flex').addClass('hidden');
    window.editUser = (id) => {
        $.get(`/api/users/${id}`, (res) => {
            const u = res.data;
            $('#u-id').val(u.id);
            $('#u-name').val(u.name);
            $('#u-email').val(u.email);
            $('#u-role').val(u.role);
            $('#u-nim').val(u.nim_nip);
            $('#u-pass').val('');
            $('#modal-title').text('Edit Pengguna');
            $('#user-modal').removeClass('hidden').addClass('flex');
        });
    };
    window.deleteUser = (id) => {
        if(id == {{ Auth::id() }}) return Swal.fire('Error', 'Tidak bisa menghapus diri sendiri', 'error');
        Swal.fire({ title: 'Hapus?', icon: 'warning', showCancelButton: true }).then(r => {
            if(r.isConfirmed) $.ajax({ url: `/api/users/${id}`, type: 'DELETE', success: () => table.ajax.reload() });
        });
    };
});
</script>
@endpush
@endsection
