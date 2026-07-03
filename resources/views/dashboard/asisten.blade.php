@extends('layouts.app')
@section('title', 'Dashboard Asisten')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Dashboard Asisten Lab</h1>
    <p class="text-slate-500 text-sm mt-1">Ringkasan tugas dan aktivitas laboratorium</p>
</div>

{{-- Quick Start Patrol --}}
@if(isset($todayPatrols) && $todayPatrols->count() > 0)
<div class="mb-6 bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-5 shadow-lg shadow-blue-500/20">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
            <h2 class="text-white font-bold text-lg">Quick Start Patroli</h2>
            <p class="text-blue-200 text-sm">Jadwal patroli Anda hari ini</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @foreach($todayPatrols as $patrol)
        <a href="{{ route('patrol.execute', $patrol->id) }}" class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl p-4 hover:bg-white/20 transition-all duration-200 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white font-semibold">{{ $patrol->laboratory->name }}</p>
                    <p class="text-blue-200 text-sm">{{ \Carbon\Carbon::parse($patrol->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($patrol->end_time)->format('H:i') }}</p>
                </div>
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center group-hover:bg-white/30 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</div>
@endif


<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 group">
        <div class="w-11 h-11 bg-gradient-to-br from-indigo-400 to-indigo-600 text-white rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-indigo-200 group-hover:shadow-indigo-300 group-hover:scale-110 transition-all duration-300 icon-shine">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['total_equipment'] }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Alat</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Total Stok: <span class="font-semibold text-slate-600">{{ $stats['total_stok'] }}</span> item</p>
        <div class="mt-2 flex gap-2">
            <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">{{ $stats['equipment_baik'] }} Baik</span>
            <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">{{ $stats['equipment_rusak'] }} Rusak</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 group">
        <div class="w-11 h-11 bg-gradient-to-br from-amber-400 to-amber-600 text-white rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-amber-200 group-hover:shadow-amber-300 group-hover:scale-110 transition-all duration-300 icon-shine">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['pending_borrowings'] }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Peminjaman Menunggu</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-lg transition-all duration-300 group">
        <div class="w-11 h-11 bg-gradient-to-br from-red-400 to-red-600 text-white rounded-xl flex items-center justify-center mb-3 shadow-lg shadow-red-200 group-hover:shadow-red-300 group-hover:scale-110 transition-all duration-300 icon-shine">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['open_damage_reports'] }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Laporan Kerusakan Aktif</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Pending Borrowings --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-700">Peminjaman Menunggu Persetujuan</h2>
            <a href="{{ route('borrowings.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Lihat Semua →</a>
        </div>
        <div class="p-5">
            @forelse($recentBorrowings as $b)
            <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                <div class="w-9 h-9 rounded-xl bg-yellow-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $b->user->name }}</p>
                    <p class="text-xs text-slate-400">{{ $b->laboratory->name }} · {{ $b->borrow_date->format('d/m/Y') }}</p>
                </div>
                <a href="{{ route('borrowings.show', $b) }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Detail</a>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Tidak ada peminjaman menunggu</p>
            @endforelse
        </div>
    </div>

    {{-- Today Schedules --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-700">Jadwal Hari Ini ({{ now()->translatedFormat('l, d F Y') }})</h2>
        </div>
        <div class="p-5">
            @forelse($todayPatrols as $s)
            <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">Patroli {{ $s->laboratory->name }}</p>
                    <p class="text-xs text-slate-400">{{ $s->day_label }} · {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</p>
                </div>
                @if($s->status === 'active')
                <a href="{{ route('patrol.execute', $s->id) }}" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">Mulai</a>
                @endif
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Tidak ada jadwal hari ini</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
