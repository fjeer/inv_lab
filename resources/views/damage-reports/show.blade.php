@extends('layouts.app')
@section('title', 'Detail Laporan Kerusakan')

@section('content')
<div class="mb-6">
    <a href="{{ route('damage-reports.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Laporan Kerusakan #{{ $damageReport->id }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-700">Detail Kerusakan</h2>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold ring-1
                    {{ $damageReport->status === 'reported' ? 'bg-yellow-50 text-yellow-700 ring-yellow-200' : '' }}
                    {{ $damageReport->status === 'in_review' ? 'bg-blue-50 text-blue-700 ring-blue-200' : '' }}
                    {{ $damageReport->status === 'in_repair' ? 'bg-indigo-50 text-indigo-700 ring-indigo-200' : '' }}
                    {{ $damageReport->status === 'repaired' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : '' }}
                    {{ $damageReport->status === 'unrepairable' ? 'bg-red-50 text-red-700 ring-red-200' : '' }}
                    {{ $damageReport->status === 'closed' ? 'bg-slate-100 text-slate-600 ring-slate-200' : '' }}
                ">{{ $damageReport->status_label }}</span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Alat</dt><dd class="text-slate-700 font-medium mt-0.5">{{ $damageReport->equipment->name }}</dd></div>
                <div><dt class="text-slate-400">Kode Unik / Item</dt><dd class="text-slate-700 font-mono text-xs font-semibold mt-0.5 bg-slate-50 px-2 py-1 rounded border border-slate-100 inline-block">{{ $damageReport->equipmentItem?->qr_code ?? '-' }}</dd></div>
                <div><dt class="text-slate-400">Lab</dt><dd class="text-slate-700 mt-0.5">{{ $damageReport->equipment->laboratory->name }}</dd></div>
                <div><dt class="text-slate-400">Pelapor</dt><dd class="text-slate-700 mt-0.5">{{ $damageReport->reporter->name }}</dd></div>
                <div><dt class="text-slate-400">Tanggal Kejadian</dt><dd class="text-slate-700 mt-0.5">{{ $damageReport->incident_date->format('d F Y') }}</dd></div>
                <div><dt class="text-slate-400">Tingkat Kerusakan</dt><dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $damageReport->damage_type === 'ringan' ? 'bg-yellow-50 text-yellow-700' : ($damageReport->damage_type === 'sedang' ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-700') }}">{{ $damageReport->damage_type_label }}</span></dd></div>
                @if($damageReport->handler)<div><dt class="text-slate-400">Ditangani oleh</dt><dd class="text-slate-700 mt-0.5">{{ $damageReport->handler->name }}</dd></div>@endif
                @if($damageReport->repair_cost > 0)<div><dt class="text-slate-400">Biaya Perbaikan</dt><dd class="text-slate-700 mt-0.5">Rp {{ number_format($damageReport->repair_cost, 0, ',', '.') }}</dd></div>@endif
            </dl>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-sm text-slate-400 font-medium">Deskripsi Kerusakan</dt>
                <dd class="text-sm text-slate-700 mt-1">{{ $damageReport->description }}</dd>
            </div>

            @if($damageReport->photo)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-sm text-slate-400 font-medium mb-2">Foto Kerusakan</dt>
                <dd class="mt-1">
                    <img src="{{ asset('storage/' . $damageReport->photo) }}" alt="Foto Kerusakan" class="max-w-full h-auto rounded-xl border border-slate-200 shadow-sm max-h-80 object-cover cursor-pointer hover:opacity-90 transition-opacity" onclick="viewPhoto('{{ asset('storage/' . $damageReport->photo) }}')">
                </dd>
            </div>
            @endif

            @if($damageReport->repair_notes)
            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-sm text-slate-400 font-medium">Catatan Perbaikan</dt>
                <dd class="text-sm text-slate-700 mt-1">{{ $damageReport->repair_notes }}</dd>
            </div>
            @endif
        </div>
    </div>

    {{-- Status Management --}}
    @if(Auth::user()->hasRole('admin_lab', 'asisten_lab') && !in_array($damageReport->status, ['closed', 'repaired', 'unrepairable']))
    <div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="font-semibold text-slate-700 text-sm mb-3">Perbarui Status</h3>
            <form id="update-status-form" class="space-y-3">
                @csrf
                <div><label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        @foreach(['in_review'=>'Ditinjau','in_repair'=>'Diperbaiki','repaired'=>'Selesai Perbaikan','unrepairable'=>'Tidak Bisa Diperbaiki','closed'=>'Ditutup'] as $v=>$l)
                        <option value="{{ $v }}" {{ $damageReport->status == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                    </select></div>
                <div><label class="block text-xs font-medium text-slate-600 mb-1">Biaya Perbaikan (Rp)</label>
                    <input type="number" name="repair_cost" value="{{ $damageReport->repair_cost }}" min="0" step="1000" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                <div><label class="block text-xs font-medium text-slate-600 mb-1">Catatan Perbaikan</label>
                    <textarea name="repair_notes" rows="3" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ $damageReport->repair_notes }}</textarea></div>
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-500 transition-colors">Perbarui Status</button>
            </form>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#update-status-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/damage-reports/{{ $damageReport->id }}/status',
            type: 'PUT',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(err) {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Gagal memperbarui status.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
    window.viewPhoto = (url) => {
        Swal.fire({
            imageUrl: url,
            imageAlt: 'Foto Kerusakan',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                popup: 'rounded-2xl overflow-hidden'
            }
        });
    };
});
</script>
@endpush
@endsection
