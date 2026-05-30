@extends('layouts.app')
@section('title', 'Kelola Alat Lab')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kelola Alat Lab</h1>
        <p class="text-slate-500 text-sm mt-1">Daftar semua peralatan laboratorium kampus</p>
    </div>
    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten'))
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Tambah Alat
    </button>
    @endif
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="equipment-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kode</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Nama Alat</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Laboratorium</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kondisi</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

{{-- Equipment Modal --}}
<div id="equipment-modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modal-title" class="font-bold text-slate-800 text-lg">Tambah Alat Baru</h3>
            <button onclick="closeModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">&times;</button>
        </div>
        <form id="equipment-form" class="p-6 space-y-4">
            <input type="hidden" name="id" id="eq-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Alat *</label>
                    <input type="text" name="name" id="eq-name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode Alat *</label>
                    <input type="text" name="code" id="eq-code" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kuantitas *</label>
                    <input type="number" name="quantity" id="eq-qty" required min="1" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Laboratorium *</label>
                    <select name="laboratory_id" id="eq-lab" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Pilih Lab</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kategori *</label>
                    <select name="category_id" id="eq-cat" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Pilih Kategori</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kondisi *</label>
                    <select name="condition" id="eq-condition" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="baik">Baik</option>
                        <option value="rusak_ringan">Rusak Ringan</option>
                        <option value="rusak_berat">Rusak Berat</option>
                        <option value="hilang">Hilang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                    <select name="status" id="eq-status" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <option value="available">Tersedia</option>
                        <option value="in_use">Digunakan</option>
                        <option value="borrowed">Dipinjam</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200">Batal</button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 shadow-lg shadow-blue-500/20 rounded-xl hover:bg-blue-700 transition-all">Simpan Alat</button>
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
            const cats = res.data.categories;
            labs.forEach(l => $('#eq-lab').append(`<option value="${l.id}">${l.name}</option>`));
            cats.forEach(c => $('#eq-cat').append(`<option value="${c.id}">${c.name}</option>`));
        }
    });

    const canManage = {{ Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten') ? 'true' : 'false' }};
    const table = $('#equipment-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/equipment',
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { data: 'code', className: 'px-5 py-4 font-mono text-xs text-slate-500' },
            { data: 'name', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'laboratory.name', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'condition', 
                render: (data) => `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${data === 'baik' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}">${data.replace('_', ' ')}</span>`
            },
            { data: 'status', render: (data) => `<span class="text-xs px-2.5 py-1 rounded-full font-medium bg-blue-50 text-blue-700">${data}</span>` },
            { 
                data: 'id', 
                className: 'px-5 py-4 text-right whitespace-nowrap',
                render: (data, type, row) => {
                    let actions = `<a href="/equipment/${data}" class="text-blue-600 hover:text-blue-700 font-medium mr-3">Detail</a>`;
                    if (canManage) {
                        actions += `
                            <button onclick="editEquipment(${data})" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</button>
                            <button onclick="deleteEquipment(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                        `;
                    }
                    return actions;
                }
            }
        ]
    });

    // Handle Form Submit
    $('#equipment-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#eq-id').val();
        const data = $(this).serialize();
        const url = id ? `/api/equipment/${id}` : '/api/equipment';
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: (res) => {
                window.showAlert('Berhasil!', res.message);
                closeModal();
                table.ajax.reload();
            },
            error: (err) => {
                const errors = err.responseJSON.errors;
                let msg = 'Gagal menyimpan data.';
                if(errors) msg = Object.values(errors).flat().join('<br>');
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    window.openModal = () => {
        $('#equipment-form')[0].reset();
        $('#eq-id').val('');
        $('#modal-title').text('Tambah Alat Baru');
        $('#equipment-modal').removeClass('hidden').addClass('flex');
    };

    window.closeModal = () => {
        $('#equipment-modal').removeClass('flex').addClass('hidden');
    };

    window.editEquipment = (id) => {
        $.get(`/api/equipment/${id}`, (res) => {
            const eq = res.data;
            $('#eq-id').val(eq.id);
            $('#eq-name').val(eq.name);
            $('#eq-code').val(eq.code);
            $('#eq-qty').val(eq.quantity);
            $('#eq-lab').val(eq.laboratory_id);
            $('#eq-cat').val(eq.category_id);
            $('#eq-condition').val(eq.condition);
            $('#eq-status').val(eq.status);
            
            $('#modal-title').text('Edit Peralatan');
            $('#equipment-modal').removeClass('hidden').addClass('flex');
        });
    };

    window.deleteEquipment = (id) => {
        Swal.fire({
            title: 'Hapus Alat?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({ url: `/api/equipment/${id}`, type: 'DELETE', success: (res) => {
                    window.showAlert('Terhapus!', res.message);
                    table.ajax.reload();
                }});
            }
        });
    };
});
</script>
@endpush
@endsection
