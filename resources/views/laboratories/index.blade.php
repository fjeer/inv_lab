@extends('layouts.app')
@section('title', 'Laboratorium')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Laboratorium</h1>
        <p class="text-slate-500 text-sm mt-1">Manajemen ruang laboratorium kampus</p>
    </div>
    @if(Auth::user()->isAdmin())
    <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all hover:-translate-y-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        Tambah Lab
    </button>
    @endif
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 overflow-hidden">
    <div class="overflow-x-auto">
        <table id="labs-table" class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kode</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Nama Lab</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Lokasi</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Kapasitas</th>
                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600">Status</th>
                    <th class="text-right px-5 py-3.5 font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div id="lab-modal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 id="modal-title" class="font-bold text-slate-800 text-lg">Tambah Laboratorium</h3>
            <button onclick="closeModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400">&times;</button>
        </div>
        <form id="lab-form" class="p-6 space-y-4">
            <input type="hidden" name="id" id="lab-id">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lab *</label>
                    <input type="text" name="name" id="lab-name" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kode *</label>
                    <input type="text" name="code" id="lab-code" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Kapasitas *</label>
                    <input type="number" name="capacity" id="lab-capacity" required min="1" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Gedung *</label>
                    <select id="lab-building" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                        <option value="">Pilih Gedung</option>
                        @foreach($buildings as $building)
                        <option value="{{ $building->id }}">{{ $building->name }} ({{ $building->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Ruangan *</label>
                    <select name="room_id" id="lab-room" required disabled class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                        <option value="">Pilih Ruangan</option>
                    </select>
                </div>
                <input type="hidden" name="location" id="lab-location-hidden">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Penanggung Jawab *</label>
                    <select name="responsible_person_id" id="lab-responsible" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                        <option value="">Pilih Penanggung Jawab</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                    <select name="status" id="lab-status" required class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
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
    const isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};
    const table = $('#labs-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/laboratories',
            dataSrc: (json) => {
                json.recordsTotal = json.meta.total;
                json.recordsFiltered = json.meta.total;
                return json.data;
            }
        },
        columns: [
            { data: 'code', className: 'px-5 py-4 font-mono text-xs text-slate-500' },
            { data: 'name', className: 'px-5 py-4 font-medium text-slate-700' },
            {
                data: 'room',
                className: 'px-5 py-4 text-slate-600',
                render: (data, type, row) => {
                    if (data && data.building) {
                        return `<div class="font-medium">${data.code}</div><div class="text-xs text-slate-400">${data.building.name}</div>`;
                    }
                    return row.location || '-';
                }
            },
            { data: 'capacity', className: 'px-5 py-4 text-slate-600' },
            {
                data: 'status',
                render: (data) => `<span class="text-xs px-2.5 py-1 rounded-full font-medium ${data === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'}">${data}</span>`
            },
            {
                data: 'id',
                className: 'px-5 py-4 text-right',
                render: (data) => {
                    let actions = `<a href="/laboratories/${data}" class="text-blue-600 hover:text-blue-700 font-medium ${isAdmin ? 'mr-3' : ''}">Detail</a>`;
                    if (isAdmin) {
                        actions = `
                            <button onclick="editLab(${data})" class="text-amber-600 hover:text-amber-700 font-medium mr-3">Edit</button>
                            ${actions}
                            <button onclick="deleteLab(${data})" class="text-red-600 hover:text-red-700 font-medium">Hapus</button>
                        `;
                    }
                    return actions;
                }
            }
        ]
    });

    $('#lab-form').on('submit', function(e) {
        e.preventDefault();
        const id = $('#lab-id').val();
        const data = $(this).serialize();
        $.ajax({
            url: id ? `/api/laboratories/${id}` : '/api/laboratories',
            type: id ? 'PUT' : 'POST',
            data: data,
            success: (res) => {
                window.showAlert('Berhasil!', res.message);
                closeModal();
                table.ajax.reload();
                window.refreshBuildings();
            },
            error: (err) => {
                const message = err.responseJSON?.message || 'Gagal memproses data.';
                Swal.fire('Error', message, 'error');
            }
        });
    });

    window.openModal = () => {
        $('#lab-form')[0].reset();
        $('#lab-id').val('');
        $('#modal-title').text('Tambah Laboratorium');
        $('#lab-modal').removeClass('hidden').addClass('flex');
    };

    window.closeModal = () => $('#lab-modal').removeClass('flex').addClass('hidden');

    let buildings = @json($buildings);

    window.refreshBuildings = () => {
        $.get('/api/buildings', (res) => {
            buildings = res.data;
            // Update the building select options as well
            $('#lab-building').html('<option value="">Pilih Gedung</option>');
            buildings.forEach(b => {
                $('#lab-building').append(`<option value="${b.id}">${b.name} (${b.code})</option>`);
            });
        });
    };

    window.updateRoomOptions = (buildingId, currentRoomId = null) => {
        $('#lab-room').html('<option value="">Pilih Ruangan</option>');
        if (buildingId) {
            const selectedBuilding = buildings.find(b => b.id == buildingId);
            if (selectedBuilding && selectedBuilding.rooms.length > 0) {
                selectedBuilding.rooms.forEach(room => {
                    const isOccupied = room.laboratories && room.laboratories.length > 0;
                    const isCurrentRoom = currentRoomId && room.id == currentRoomId;
                    
                    let text = `${room.name} (${room.code})`;
                    let disabled = '';
                    if (isOccupied && !isCurrentRoom) {
                        text += ' (Terpakai)';
                        disabled = 'disabled';
                    }
                    
                    $('#lab-room').append(`<option value="${room.id}" ${disabled}>${text}</option>`);
                });
                $('#lab-room').prop('disabled', false);
            } else {
                $('#lab-room').prop('disabled', true);
            }
        } else {
            $('#lab-room').prop('disabled', true);
        }
        updateLocationHidden();
    };

    $('#lab-building').on('change', function() {
        window.updateRoomOptions($(this).val());
    });

    $('#lab-room').on('change', updateLocationHidden);

    function updateLocationHidden() {
        const bName = $('#lab-building option:selected').text();
        let rName = $('#lab-room option:selected').text();
        
        // Strip suffix (Terpakai) if exists
        rName = rName.replace(' (Terpakai)', '');

        if ($('#lab-building').val() && $('#lab-room').val()) {
            $('#lab-location-hidden').val(`${bName} - ${rName}`);
        } else {
            $('#lab-location-hidden').val('');
        }
    }

    window.editLab = (id) => {
        $.get(`/api/laboratories/${id}`, (res) => {
            const lab = res.data;
            $('#lab-id').val(lab.id);
            $('#lab-name').val(lab.name);
            $('#lab-code').val(lab.code);
            $('#lab-capacity').val(lab.capacity);
            $('#lab-responsible').val(lab.responsible_person_id);
            $('#lab-status').val(lab.status);

            if (lab.room) {
                $('#lab-building').val(lab.room.building_id);
                window.updateRoomOptions(lab.room.building_id, lab.room_id);
                $('#lab-room').val(lab.room_id);
                $('#lab-location-hidden').val(lab.location);
            } else {
                $('#lab-building').val('').trigger('change');
            }
            $('#modal-title').text('Edit Laboratorium');
            $('#lab-modal').removeClass('hidden').addClass('flex');
        });
    };

    window.deleteLab = (id) => {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data laboratorium akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/api/laboratories/${id}`,
                    type: 'DELETE',
                    success: (res) => {
                        window.showAlert('Terhapus!', res.message);
                        table.ajax.reload();
                        window.refreshBuildings();
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
