@extends('layouts.app')
@section('title', 'Kondisi Barang')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Kondisi Barang</h1>
        <p class="text-slate-500 text-sm mt-1">Monitoring kondisi alat laboratorium</p>
    </div>
    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten'))
    <div class="flex gap-2">
        <button type="button" onclick="openScannerModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl shadow-sm hover:bg-slate-50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            Scan QR
        </button>
        <a href="{{ route('conditions.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Catat Pemeriksaan
        </a>
    </div>
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

<!-- Modal Scanner QR -->
<div id="scanner-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-xl">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-slate-800 text-lg">Scan QR Code Alat</h3>
                <p class="text-xs text-slate-500 mt-0.5">Arahkan kamera ke QR Code pada alat</p>
            </div>
            <button onclick="closeScannerModal()" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 flex flex-col items-center">
            <div id="qr-reader" class="w-full rounded-xl overflow-hidden border-2 border-dashed border-slate-300 bg-slate-50" style="min-height:250px;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;

window.openScannerModal = function() {
    $('#scanner-modal').removeClass('hidden').addClass('flex');
    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("qr-reader");
    }
    
    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            // Stop scanning once decoded
            html5QrCode.stop().then(() => {
                $('#scanner-modal').removeClass('flex').addClass('hidden');
                
                // Fetch equipment via API
                Swal.fire({ title: 'Memproses...', text: 'Mencari data alat...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                
                $.ajax({
                    url: '/api/equipment/scan',
                    type: 'POST',
                    data: { qr_code: decodedText },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        Swal.close();
                        const labId = res.data.equipment.laboratory_id;
                        const eqId = res.data.equipment_id;
                        const itemId = res.data.id;
                        window.location.href = `/conditions-create?lab_id=${labId}&eq_id=${eqId}&item_id=${itemId}`;
                    },
                    error: function(err) {
                        Swal.fire('Error', err.responseJSON?.message || 'Gagal memindai alat', 'error');
                    }
                });
            });
        },
        (errorMessage) => {
            // Scanning in progress
        }
    ).catch(err => {
        console.error("Unable to start scanning", err);
        Swal.fire('Error', 'Kamera tidak dapat diakses atau tidak ada izin.', 'error');
        $('#scanner-modal').removeClass('flex').addClass('hidden');
    });
};

window.closeScannerModal = function() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop();
    }
    $('#scanner-modal').removeClass('flex').addClass('hidden');
};

$(document).ready(function() {
    const canManage = {{ Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten') ? 'true' : 'false' }};
    const table = $('#conditions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/conditions',
            data: function (d) {
                d.laboratory_id = $('#filter-lab').val();
                d.equipment_id = $('#filter-equipment').val();
                d.condition = $('#filter-condition').val();
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
                data: 'check_date', 
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
                    let item = row.equipment_item;
                    if (item && item.qr_code) {
                        name += `<br><span class="text-xs font-mono text-slate-400 bg-slate-50 border border-slate-100 rounded px-1">${item.qr_code}</span>`;
                    }
                    return name;
                }
            },
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
                ${rowData.equipment_item ? `
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-xs text-slate-400 font-medium block">Kode Unik / Item</span>
                    <span class="font-mono text-xs text-slate-700 font-semibold mt-0.5 bg-slate-50 px-2 py-1 rounded border border-slate-100 inline-block">${rowData.equipment_item.qr_code}</span>
                </div>` : ''}
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
