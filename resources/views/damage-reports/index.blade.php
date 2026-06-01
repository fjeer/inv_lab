@extends('layouts.app')
@section('title', 'Laporan Kerusakan')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Laporan Kerusakan</h1>
        <p class="text-slate-500 text-sm mt-1">Pelaporan dan penanganan kerusakan alat lab</p>
    </div>
    <a href="{{ route('damage-reports.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Buat Laporan
    </a>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-3">
        <select id="filter-status" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Status</option>
            @foreach(['reported'=>'Dilaporkan','in_review'=>'Ditinjau','in_repair'=>'Diperbaiki','repaired'=>'Selesai','unrepairable'=>'Tidak Bisa Diperbaiki','closed'=>'Ditutup'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>@endforeach
        </select>
        <select id="filter-damage-type" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Tingkat</option>
            @foreach(['ringan'=>'Ringan','sedang'=>'Sedang','berat'=>'Berat'] as $v=>$l)
            <option value="{{ $v }}">{{ $l }}</option>@endforeach
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
        <table id="reports-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Tgl Kejadian</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Alat</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Lab</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Pelapor</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Tingkat</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Status</th>
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
    const table = $('#reports-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/damage-reports',
            data: function (d) {
                d.status = $('#filter-status').val();
                d.damage_type = $('#filter-damage-type').val();
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
                data: 'incident_date', 
                className: 'px-5 py-4 text-slate-700',
                render: function(data) {
                    return data ? data.substring(0, 10) : '-';
                }
            },
            { 
                data: 'equipment.name', 
                className: 'px-5 py-4 font-medium text-slate-700',
                render: function(data, type, row) {
                    let name = data;
                    let item = row.equipment_item || row.equipmentItem;
                    if (item && item.qr_code) {
                        name += `<br><span class="text-xs font-mono text-slate-400 bg-slate-50 border border-slate-100 rounded px-1">${item.qr_code}</span>`;
                    }
                    return name;
                }
            },
            { data: 'equipment.laboratory.name', className: 'px-5 py-4 text-slate-600' },
            { data: 'reporter.name', className: 'px-5 py-4 text-slate-600' },
            { 
                data: 'damage_type', 
                className: 'px-5 py-4',
                render: function(data) {
                    let label = data === 'ringan' ? 'Ringan' : (data === 'sedang' ? 'Sedang' : 'Berat');
                    let color = data === 'ringan' ? 'bg-yellow-50 text-yellow-700' : (data === 'sedang' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700');
                    return `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${color}">${label}</span>`;
                }
            },
            { 
                data: 'status', 
                className: 'px-5 py-4',
                render: function(data) {
                    let labels = {
                        'reported': 'Dilaporkan',
                        'in_review': 'Ditinjau',
                        'in_repair': 'Diperbaiki',
                        'repaired': 'Selesai',
                        'unrepairable': 'Tidak Bisa Diperbaiki',
                        'closed': 'Ditutup'
                    };
                    let colors = {
                        'reported': 'bg-yellow-50 text-yellow-700',
                        'in_review': 'bg-blue-50 text-blue-700',
                        'in_repair': 'bg-indigo-50 text-indigo-700',
                        'repaired': 'bg-emerald-50 text-emerald-700',
                        'unrepairable': 'bg-red-50 text-red-700',
                        'closed': 'bg-slate-100 text-slate-600'
                    };
                    return `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${colors[data] || ''}">${labels[data] || data}</span>`;
                }
            },
            { 
                data: 'id', 
                className: 'px-5 py-4 text-right',
                render: function(data, type, row) {
                    let actions = `<a href="/damage-reports/${data}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Detail</a>`;
                    @if(Auth::user()->hasRole('admin_lab', 'admin'))
                    if (!row.deleted_at) {
                        actions += `<button onclick="deleteDamageReport(${data})" class="ml-3 text-xs text-red-600 hover:text-red-700 font-medium">Hapus</button>`;
                    } else {
                        actions += `<button onclick="forceDeleteDamageReport(${data})" class="ml-3 text-xs text-red-700 hover:text-red-800 font-medium">Hapus Permanen</button>`;
                    }
                    @endif
                    return actions;
                }
            }
        ]
    });

    $('#btn-filter').click(function() {
        table.ajax.reload();
    });

    window.deleteDamageReport = (id) => {
        Swal.fire({
            title: 'Hapus Laporan Kerusakan?',
            text: 'Laporan akan dipindahkan ke Data Terhapus dan masih dapat ditampilkan lewat filter.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: `/api/damage-reports/${id}`,
                type: 'DELETE',
                success: function(res) {
                    window.showAlert('Berhasil!', res.message, 'success');
                    table.ajax.reload();
                },
                error: function(err) {
                    Swal.fire('Error', err.responseJSON?.message || 'Gagal menghapus laporan kerusakan.', 'error');
                }
            });
        });
    };

    window.forceDeleteDamageReport = (id) => {
        Swal.fire({
            title: 'Hapus Permanen?',
            text: 'Laporan kerusakan akan dihapus permanen dan tidak bisa direstore.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Permanen',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: `/api/damage-reports/${id}/force`,
                type: 'DELETE',
                success: function(res) {
                    window.showAlert('Berhasil!', res.message, 'success');
                    table.ajax.reload();
                },
                error: function(err) {
                    Swal.fire('Error', err.responseJSON?.message || 'Gagal menghapus permanen laporan kerusakan.', 'error');
                }
            });
        });
    };
});
</script>
@endpush
