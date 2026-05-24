@extends('layouts.app')
@section('title', $laboratory->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('laboratories.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">{{ $laboratory->name }}</h1>
    <p class="text-sm text-slate-400 font-mono">{{ $laboratory->code }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-semibold text-slate-700 mb-4">Informasi Laboratorium</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Lokasi</dt><dd class="text-slate-700 mt-0.5">{{ $laboratory->location ?? '-' }}</dd></div>
                <div><dt class="text-slate-400">Kapasitas</dt><dd class="text-slate-700 mt-0.5">{{ $laboratory->capacity ?? '-' }} orang</dd></div>
                <div><dt class="text-slate-400">Penanggung Jawab</dt><dd class="text-slate-700 mt-0.5">{{ $laboratory->responsiblePerson?->name ?? '-' }}</dd></div>
                <div><dt class="text-slate-400">Status</dt><dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $laboratory->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($laboratory->status) }}</span></dd></div>
            </dl>
            @if($laboratory->description)
            <p class="text-sm text-slate-600 mt-4 pt-4 border-t border-slate-100">{{ $laboratory->description }}</p>
            @endif
        </div>

        {{-- Equipment List --}}
        @if(!Auth::user()->isPengguna())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-700">Daftar Alat ({{ $laboratory->equipment->count() }})</h2></div>
            <div class="divide-y divide-slate-50">
                @forelse($laboratory->equipment as $eq)
                <div class="px-6 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-700">{{ $eq->name }}</p>
                        <p class="text-xs text-slate-400">{{ $eq->code }} · {{ $eq->condition_label }}</p>
                    </div>
                    <a href="{{ route('equipment.show', $eq) }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Detail</a>
                </div>
                @empty
                <p class="px-6 py-6 text-sm text-slate-400 text-center">Belum ada alat</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        {{-- Schedules --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100"><h3 class="font-semibold text-slate-700 text-sm">Jadwal Aktif</h3></div>
            <div class="p-5">
                @forelse($laboratory->schedules->where('status', 'active')->take(5) as $s)
                <div class="py-2 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                    <p class="text-xs font-medium text-slate-700">{{ $s->title }}</p>
                    <p class="text-xs text-slate-400">{{ $s->day_label }} · {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</p>
                </div>
                @empty
                <p class="text-xs text-slate-400 text-center py-3">Belum ada jadwal</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
