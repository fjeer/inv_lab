@extends('layouts.app')
@section('title', 'Tambah Laboratorium')

@section('content')
<div class="mb-6">
    <a href="{{ route('laboratories.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Tambah Laboratorium</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('laboratories.store') }}" class="space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lab *</label><input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kode *</label><input type="text" name="code" value="{{ old('code') }}" required placeholder="LAB-XXX" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Gedung *</label>
                <select id="building_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" required>
                    <option value="">Pilih Gedung</option>
                    @foreach($buildings as $building)
                    <option value="{{ $building->id }}">{{ $building->name }} ({{ $building->code }})</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Ruangan *</label>
                <select name="room_id" id="room_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" required disabled>
                    <option value="">Pilih Ruangan</option>
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kapasitas</label><input type="number" name="capacity" value="{{ old('capacity') }}" min="1" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Penanggung Jawab</label>
                <select name="responsible_person_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="">Pilih Penanggung Jawab</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('responsible_person_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ ucfirst(str_replace('_', ' ', $user->role)) }})</option>
                    @endforeach
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>
            <input type="hidden" name="location" id="location">
        </div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label><textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('description') }}</textarea></div>

        <script>
            const buildings = @json($buildings);
            const buildingSelect = document.getElementById('building_id');
            const roomSelect = document.getElementById('room_id');
            const locationInput = document.getElementById('location');

            buildingSelect.addEventListener('change', function() {
                const buildingId = this.value;
                roomSelect.innerHTML = '<option value="">Pilih Ruangan</option>';
                
                if (buildingId) {
                    const selectedBuilding = buildings.find(b => b.id == buildingId);
                    if (selectedBuilding && selectedBuilding.rooms.length > 0) {
                        selectedBuilding.rooms.forEach(room => {
                            const isOccupied = room.laboratories && room.laboratories.length > 0;
                            if (!isOccupied) {
                                const option = document.createElement('option');
                                option.value = room.id;
                                option.textContent = `${room.name} (${room.code})`;
                                roomSelect.appendChild(option);
                            }
                        });
                        roomSelect.disabled = false;
                    } else {
                        roomSelect.disabled = true;
                    }
                } else {
                    roomSelect.disabled = true;
                }
                updateLocation();
            });

            roomSelect.addEventListener('change', updateLocation);

            function updateLocation() {
                const bName = buildingSelect.options[buildingSelect.selectedIndex]?.text || '';
                const rName = roomSelect.options[roomSelect.selectedIndex]?.text || '';
                if (bName && rName && roomSelect.value) {
                    locationInput.value = `${bName} - ${rName}`;
                } else {
                    locationInput.value = '';
                }
            }
        </script>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('laboratories.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Simpan</button>
        </div>
    </form>
</div>
@endsection
