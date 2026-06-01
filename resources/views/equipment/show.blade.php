@extends('layouts.app')
@section('title', $equipment->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('equipment.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali ke Daftar Alat</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">{{ $equipment->name }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-semibold text-slate-700 mb-4">Informasi Alat</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400 font-medium">Kode Inventaris</dt><dd class="text-slate-700 font-mono mt-0.5">{{ $equipment->code }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Laboratorium</dt><dd class="text-slate-700 mt-0.5">{{ $equipment->laboratory->name }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Kategori</dt><dd class="text-slate-700 mt-0.5">{{ $equipment->category?->name ?? '-' }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Merk / Model</dt><dd class="text-slate-700 mt-0.5">{{ $equipment->brand ?? '-' }} {{ $equipment->model }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Serial Number</dt><dd class="text-slate-700 mt-0.5">{{ $equipment->serial_number ?? '-' }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Tahun Perolehan</dt><dd class="text-slate-700 mt-0.5">{{ $equipment->year_acquired ?? '-' }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Harga</dt><dd class="text-slate-700 mt-0.5">Rp {{ number_format($equipment->price, 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-400 font-medium">Jumlah</dt><dd class="text-slate-700 mt-0.5">{{ $equipment->quantity }}</dd></div>
                <div>
                    <dt class="text-slate-400 font-medium">Kondisi</dt>
                    <dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $equipment->condition === 'baik' ? 'bg-emerald-50 text-emerald-700' : ($equipment->condition === 'rusak_ringan' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">{{ $equipment->condition_label }}</span></dd>
                </div>
                <div>
                    <dt class="text-slate-400 font-medium">Status</dt>
                    <dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $equipment->status === 'available' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">{{ $equipment->status_label }}</span></dd>
                </div>
            </dl>
            @if($equipment->description)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-sm text-slate-400 font-medium">Deskripsi</dt>
                <dd class="text-sm text-slate-700 mt-1">{{ $equipment->description }}</dd>
            </div>
            @endif
        </div>

        {{-- Physical Items List --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-slate-700">Daftar Item Fisik (Stok)</h2>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">{{ $equipment->items->count() }} Total</span>
                </div>
                <form method="GET" action="{{ route('equipment.show', $equipment) }}" class="flex items-center gap-2">
                    <label for="item_status" class="text-xs font-semibold text-slate-500">Tampilkan</label>
                    <select id="item_status" name="item_status" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600">
                        <option value="active" @selected(request('item_status', 'active') === 'active')>Aktif</option>
                        <option value="with" @selected(request('item_status') === 'with')>Semua</option>
                        <option value="only" @selected(request('item_status') === 'only')>Terhapus</option>
                    </select>
                </form>
            </div>
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="text-left px-4 py-2 font-semibold text-slate-600">No. Urut</th>
                            <th class="text-left px-4 py-2 font-semibold text-slate-600">QR Code / Kode Unik</th>
                            <th class="text-center px-4 py-2 font-semibold text-slate-600">Kondisi</th>
                            <th class="text-left px-4 py-2 font-semibold text-slate-600">Histori Penggantian</th>
                            <th class="text-right px-4 py-2 font-semibold text-slate-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($equipment->items as $item)
                        <tr class="{{ $item->trashed() ? 'bg-red-50/40' : '' }}">
                            <td class="px-4 py-3 font-medium text-slate-700">#{{ $item->sequence_number }}</td>
                            <td class="px-4 py-3">
                                <div class="font-mono text-xs text-slate-600">{{ $item->qr_code }}</div>
                                @if($item->trashed())
                                    <div class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-red-600">Soft deleted</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-xs px-2.5 py-0.5 rounded-full font-medium 
                                    {{ $item->condition === 'baik' ? 'bg-emerald-50 text-emerald-700' : '' }}
                                    {{ $item->condition === 'rusak_ringan' ? 'bg-amber-50 text-amber-700' : '' }}
                                    {{ $item->condition === 'rusak_berat' ? 'bg-red-50 text-red-700' : '' }}
                                    {{ $item->condition === 'hilang' ? 'bg-slate-100 text-slate-600' : '' }}
                                ">{{ $item->condition_label }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">
                                @if($item->replacesEquipmentItem)
                                    <div class="flex flex-col gap-0.5 text-amber-600">
                                        <span class="font-medium">Menggantikan:</span>
                                        <span class="font-mono text-[10px] bg-amber-50 border border-amber-100 px-1 py-0.5 rounded">{{ $item->replacesEquipmentItem->qr_code }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($item->trashed())
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('equipment-items.restore', [$equipment, $item->id]) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">Restore</button>
                                        </form>
                                        <form method="POST" action="{{ route('equipment-items.force-destroy', [$equipment, $item->id]) }}" data-confirm-title="Hapus Permanen Item?" data-confirm-text="Data tidak bisa direstore setelah dihapus permanen." data-confirm-button="Ya, Hapus Permanen">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus Permanen</button>
                                        </form>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('equipment-items.destroy', [$equipment, $item]) }}" data-confirm-title="Hapus Item?" data-confirm-text="Item akan dipindahkan ke data terhapus dan masih bisa direstore." data-confirm-button="Ya, Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-400 py-4">Belum ada item fisik terdaftar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Condition History --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-700">Riwayat Kondisi</h2>
            </div>
            <div class="p-6">
                @forelse($equipment->conditions->take(10) as $cond)
                <div class="flex gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $cond->condition === 'baik' ? 'bg-emerald-500' : ($cond->condition === 'rusak_ringan' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $cond->condition_label }}</p>
                        <p class="text-xs text-slate-400">{{ $cond->check_date->format('d/m/Y') }} · {{ $cond->checker->name }}</p>
                        @if($cond->description)<p class="text-xs text-slate-500 mt-1">{{ $cond->description }}</p>@endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-slate-400 text-center py-4">Belum ada riwayat kondisi</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        @if($equipment->photo)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 text-center">
            <img src="{{ asset('storage/' . $equipment->photo) }}" alt="{{ $equipment->name }}" class="w-full h-auto rounded-xl max-h-64 object-cover border border-slate-100">
        </div>
        @endif

        {{-- Damage Reports --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-semibold text-slate-700 text-sm">Laporan Kerusakan</h3>
            </div>
            <div class="p-5">
                @forelse($equipment->damageReports->take(5) as $dr)
                <div class="py-2 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                    <p class="text-xs font-medium text-slate-700">{{ $dr->damage_type_label }} - {{ $dr->status_label }}</p>
                    <p class="text-xs text-slate-400">{{ $dr->incident_date->format('d/m/Y') }}</p>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-3">Tidak ada laporan kerusakan</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('form[data-confirm-title]').on('submit', function(e) {
        e.preventDefault();

        const form = this;
        Swal.fire({
            title: form.dataset.confirmTitle,
            text: form.dataset.confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: form.dataset.confirmButton,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
