@extends('layouts.app')
@section('title', 'Buat Laporan Kerusakan')

@section('content')
<div class="mb-6">
    <a href="{{ route('damage-reports.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Buat Laporan Kerusakan</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    @if($errors->any())<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>@endif

    <form id="damage-report-form" class="space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Laboratorium *</label>
                <select name="laboratory_id" id="laboratory_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="">Pilih Lab</option>
                    @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Alat yang Rusak *</label>
                <select name="equipment_id" id="equipment_id" required disabled class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" data-old="{{ old('equipment_id') }}">
                    <option value="">Pilih Alat</option>
                </select>
            </div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tingkat Kerusakan *</label>
                <select name="damage_type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @foreach(['ringan'=>'Ringan','sedang'=>'Sedang','berat'=>'Berat'] as $v=>$l)<option value="{{ $v }}" {{ old('damage_type') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Kejadian *</label>
                <input type="date" name="incident_date" value="{{ old('incident_date', date('Y-m-d')) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto Kerusakan</label>
                <input type="file" name="photo" accept="image/*" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-amber-50 file:text-amber-700">
            </div>
        </div>

        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi Kerusakan *</label>
            <textarea name="description" rows="4" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="Jelaskan detail kerusakan yang terjadi...">{{ old('description') }}</textarea></div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('damage-reports.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 transition-all">Kirim Laporan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const equipmentList = @json($equipmentList);
    const labSelect = document.getElementById('laboratory_id');
    const eqSelect = document.getElementById('equipment_id');

    function updateEquipmentOptions(labId) {
        eqSelect.innerHTML = '<option value="">Pilih Alat</option>';
        
        if (labId) {
            const filtered = equipmentList.filter(eq => eq.laboratory_id == labId);
            if (filtered.length > 0) {
                filtered.forEach(eq => {
                    const option = document.createElement('option');
                    option.value = eq.id;
                    option.textContent = `${eq.name} (${eq.code})`;
                    eqSelect.appendChild(option);
                });
                eqSelect.disabled = false;
                
                // Set old value if exists
                const oldEq = eqSelect.dataset.old;
                if (oldEq) {
                    eqSelect.value = oldEq;
                }
            } else {
                eqSelect.disabled = true;
            }
        } else {
            eqSelect.disabled = true;
        }
    }

    labSelect.addEventListener('change', function() {
        updateEquipmentOptions(this.value);
    });

    // Initialize on load
    if (labSelect.value) {
        updateEquipmentOptions(labSelect.value);
    }

    $('#damage-report-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: '/api/damage-reports',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("damage-reports.index") }}';
                }, 1500);
            },
            error: function(err) {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Gagal memproses data.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
</script>
@endpush
@endsection
