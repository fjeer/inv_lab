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
