@extends('layouts.app')
@section('title', 'Kondisi Barang')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kondisi Barang</h1>
        <p class="text-slate-500 text-sm mt-1">Monitoring kondisi alat laboratorium</p>
    </div>
    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab'))
    <a href="{{ route('conditions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Catat Pemeriksaan
    </a>
    @endif
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <select id="filter-lab" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Lab</option>
            @foreach($laboratories as $lab)
            <option value="{{ $lab->id }}">{{ $lab->name }}</option>
            @endforeach
        </select>
        <select id="filter-equipment" class="flex-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Alat</option>
            @foreach($equipmentList as $eq)<option value="{{ $eq->id }}">{{ $eq->name }} ({{ $eq->code }})</option>@endforeach
        </select>
        <select id="filter-condition" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Kondisi</option>
            @foreach(['baik'=>'Baik','rusak_ringan'=>'Rusak Ringan','rusak_berat'=>'Rusak Berat','hilang'=>'Hilang'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>@endforeach
        </select>
        <button type="button" id="btn-filter" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-colors">Filter</button>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="conditions-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Tanggal</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Alat</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Lab</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kondisi</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Sebelumnya</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Pemeriksa</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Keterangan</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Foto</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const canManage = {{ Auth::user()->hasRole('admin_lab', 'asisten_lab') ? 'true' : 'false' }};
    const table = $('#conditions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/conditions',
            data: function (d) {
                d.laboratory_id = $('#filter-lab').val();
                d.equipment_id = $('#filter-equipment').val();
                d.condition = $('#filter-condition').val();
            },
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { 
                data: 'check_date', 
                className: 'px-5 py-4 text-slate-700',
                render: function(data) {
                    return data ? data.substring(0, 10) : '-';
                }
            },
            { data: 'equipment.name', className: 'px-5 py-4 font-medium text-slate-700' },
            { data: 'equipment.laboratory.name', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'condition', 
                className: 'px-5 py-4',
                render: function(data) {
                    let text = data.replace('_', ' ');
                    let color = data === 'baik' ? 'bg-emerald-50 text-emerald-700' : (data === 'rusak_ringan' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700');
                    return `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${color} capitalize">${text}</span>`;
                }
            },
            { 
                data: 'previous_condition', 
                className: 'px-5 py-4 text-slate-500 text-xs capitalize',
                render: function(data) {
                    return data ? data.replace('_', ' ') : '-';
                }
            },
            { data: 'checker.name', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'description', 
                className: 'px-5 py-4 text-slate-500 max-w-40 truncate',
                render: function(data) {
                    return data ? data : '-';
                }
            },
            {
                data: 'photo',
                className: 'px-5 py-4',
                render: function(data) {
                    if (data) {
                        return `<img src="/storage/${data}" class="w-10 h-10 object-cover rounded-lg border border-slate-100 shadow-sm cursor-pointer hover:scale-110 transition-transform" onclick="viewPhoto('/storage/${data}')">`;
                    }
                    return '-';
                }
            },
            {
                data: 'id',
                className: 'px-5 py-4 text-right whitespace-nowrap',
                render: function(data, type, row) {
                    let actions = `<button onclick="showConditionDetail(${data})" class="text-blue-600 hover:text-blue-700 font-medium mr-3">Detail</button>`;
                    if (canManage) {
                        actions += `<button onclick="deleteCondition(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>`;
                    }
                    return actions;
                }
            }
        ]
    });

    $('#btn-filter').click(function() {
        table.ajax.reload();
    });

    window.viewPhoto = (url) => {
        Swal.fire({
            imageUrl: url,
            imageAlt: 'Foto Kondisi Barang',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-2xl overflow-hidden'
            }
        });
    };

    window.showConditionDetail = (id) => {
        const rowData = table.rows().data().toArray().find(r => r.id === id);
        if (!rowData) return;

        let photoHtml = rowData.photo 
            ? `<div class="mb-4"><img src="/storage/${rowData.photo}" class="w-full h-48 object-cover rounded-xl border border-slate-100 shadow-sm cursor-pointer" onclick="viewPhoto('/storage/${rowData.photo}')"></div>` 
            : '';

        let content = `
            <div class="text-left text-sm space-y-3.5">
                ${photoHtml}
                <div class="grid grid-cols-2 gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Nama Alat</span>
                        <span class="font-semibold text-slate-700">${rowData.equipment.name}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Kode Alat</span>
                        <span class="font-mono text-xs text-slate-700 font-medium">${rowData.equipment.code}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Laboratorium</span>
                        <span class="text-slate-600 font-medium">${rowData.equipment.laboratory.name}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Tanggal Pemeriksaan</span>
                        <span class="text-slate-600 font-medium">${rowData.check_date.substring(0, 10)}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pb-3 border-b border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Kondisi</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium inline-block mt-0.5 capitalize ${rowData.condition === 'baik' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'}">${rowData.condition.replace('_', ' ')}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Kondisi Sebelumnya</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium inline-block mt-0.5 bg-slate-100 text-slate-600 capitalize">${(rowData.previous_condition || '-').replace('_', ' ')}</span>
                    </div>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-xs text-slate-400 font-medium block">Pemeriksa</span>
                    <span class="text-slate-700 font-medium">${rowData.checker.name}</span>
                </div>
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-xs text-slate-400 font-medium block">Keterangan / Temuan</span>
                    <p class="text-slate-600 mt-0.5">${rowData.description || '-'}</p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-medium block">Tindakan yang Diambil</span>
                    <p class="text-slate-600 mt-0.5">${rowData.action_taken || '-'}</p>
                </div>
            </div>
        `;

        Swal.fire({
            title: '<h3 class="font-bold text-slate-800 text-lg mb-2">Detail Pemeriksaan Kondisi</h3>',
            html: content,
            showCloseButton: true,
            showConfirmButton: false,
            width: '450px',
            customClass: {
                popup: 'rounded-2xl overflow-hidden p-6'
            }
        });
    };

    window.deleteCondition = (id) => {
        Swal.fire({
            title: 'Hapus Catatan?',
            text: "Data pemeriksaan kondisi barang ini akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/conditions/${id}`,
                    type: 'DELETE',
                    success: (res) => {
                        window.showAlert('Terhapus!', res.message);
                        table.ajax.reload();
                    },
                    error: (err) => {
                        const message = err.responseJSON?.message || 'Gagal menghapus catatan.';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    };
});
</script>
@endpush
