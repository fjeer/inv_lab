@extends('layouts.app')
@section('title', 'Buat Pengajuan Pengadaan')

@section('content')
<div class="mb-6">
    <a href="{{ route('procurements.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Buat Pengajuan Pengadaan</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-3xl">
    @if($errors->any())<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>@endif

    <form class="space-y-6" id="procurement-form">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Judul Pengadaan *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="Pengadaan Alat Lab Pemrograman"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Prioritas *</label>
                <select name="priority" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @foreach(['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi','urgent'=>'Mendesak'] as $v=>$l)<option value="{{ $v }}" {{ old('priority', 'medium') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('description') }}</textarea></div>

        {{-- Dynamic items --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="text-sm font-medium text-slate-700">Item Pengadaan *</label>
                <button type="button" id="add-item" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">+ Tambah Item</button>
            </div>
            <div id="items-container" class="space-y-3">
                <div class="item-row grid grid-cols-12 gap-2 items-end bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="col-span-4"><label class="block text-xs text-slate-500 mb-1">Nama Item *</label><input type="text" name="items[0][item_name]" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div class="col-span-4"><label class="block text-xs text-slate-500 mb-1">Menggantikan (Opsional)</label>
                        <select name="items[0][replaces_equipment_item_id]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                            <option value="">- Alat Baru (Bukan Pengganti) -</option>
                            @foreach($equipmentList as $eq)
                                @if($eq->items->count() > 0)
                                <optgroup label="{{ $eq->name }} ({{ $eq->code }})">
                                    @foreach($eq->items as $item)
                                    <option value="{{ $item->id }}">{{ $item->qr_code }} [Kondisi: {{ strtoupper($item->condition) }}]</option>
                                    @endforeach
                                </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-4"><label class="block text-xs text-slate-500 mb-1">Spesifikasi</label><input type="text" name="items[0][specification]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div class="col-span-2 mt-2"><label class="block text-xs text-slate-500 mb-1">Qty *</label><input type="number" name="items[0][quantity]" value="1" min="1" required class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div class="col-span-2 mt-2"><label class="block text-xs text-slate-500 mb-1">Satuan *</label><input type="text" name="items[0][unit]" value="unit" required class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div class="col-span-7 mt-2"><label class="block text-xs text-slate-500 mb-1">Harga Estimasi *</label><input type="number" name="items[0][estimated_price]" min="0" required class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div class="col-span-1 mt-2 flex justify-center"><button type="button" class="remove-item p-2 text-slate-400 hover:text-red-500 transition-colors" title="Hapus"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
                </div>
            </div>
        </div>

        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
            <textarea name="notes" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('notes') }}</textarea></div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('procurements.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Ajukan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = 1;
const equipmentOptions = `@foreach($equipmentList as $eq)@if($eq->items->count() > 0)<optgroup label="{{ $eq->name }} ({{ $eq->code }})">@foreach($eq->items as $item)<option value="{{ $item->id }}">{{ $item->qr_code }} [Kondisi: {{ strtoupper($item->condition) }}]</option>@endforeach</optgroup>@endif @endforeach`;

document.getElementById('add-item').addEventListener('click', function() {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row grid grid-cols-12 gap-2 items-end bg-slate-50 p-3 rounded-xl border border-slate-200';
    row.innerHTML = `
        <div class="col-span-4"><label class="block text-xs text-slate-500 mb-1">Nama Item *</label><input type="text" name="items[\${itemIndex}][item_name]" required class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        <div class="col-span-4"><label class="block text-xs text-slate-500 mb-1">Menggantikan (Opsional)</label><select name="items[\${itemIndex}][replaces_equipment_item_id]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"><option value="">- Alat Baru (Bukan Pengganti) -</option>\${equipmentOptions}</select></div>
        <div class="col-span-4"><label class="block text-xs text-slate-500 mb-1">Spesifikasi</label><input type="text" name="items[\${itemIndex}][specification]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        <div class="col-span-2 mt-2"><label class="block text-xs text-slate-500 mb-1">Qty *</label><input type="number" name="items[\${itemIndex}][quantity]" value="1" min="1" required class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        <div class="col-span-2 mt-2"><label class="block text-xs text-slate-500 mb-1">Satuan *</label><input type="text" name="items[\${itemIndex}][unit]" value="unit" required class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        <div class="col-span-7 mt-2"><label class="block text-xs text-slate-500 mb-1">Harga Estimasi *</label><input type="number" name="items[\${itemIndex}][estimated_price]" min="0" required class="w-full px-2 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        <div class="col-span-1 mt-2 flex justify-center"><button type="button" class="remove-item p-2 text-slate-400 hover:text-red-500 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></div>
    `;
    container.appendChild(row);
    itemIndex++;
});
document.getElementById('items-container').addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        const rows = this.querySelectorAll('.item-row');
        if (rows.length > 1) { e.target.closest('.item-row').remove(); }
    }
});

$(document).ready(function() {
    $('#procurement-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/procurements',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("procurements.index") }}';
                }, 1500);
            },
            error: function(err) {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Gagal memproses data.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
@endpush
@endsection
