@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Halo, {{ Auth::user()->name }}!</h1>
    <p class="text-slate-500 text-sm mt-1">Ringkasan aktivitas Anda di sistem laboratorium</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['my_borrowings'] }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Total Peminjaman</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-yellow-50 rounded-xl flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['pending_borrowings'] }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Menunggu Persetujuan</p>
    </div>

    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
        <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center mb-3">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <p class="text-2xl font-bold text-slate-800">{{ $stats['my_damage_reports'] }}</p>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Laporan Kerusakan Saya</p>
    </div>
</div>

{{-- Quick Actions --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('borrowings.create') }}" class="group bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-5 text-white shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all duration-300 hover:-translate-y-0.5">
        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center mb-3 group-hover:bg-white/30 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        </div>
        <h3 class="font-semibold text-lg">Pinjam Lab</h3>
        <p class="text-blue-100 text-sm mt-1">Ajukan peminjaman laboratorium</p>
    </a>

    <a href="{{ route('damage-reports.create') }}" class="group bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/20 hover:shadow-amber-500/30 transition-all duration-300 hover:-translate-y-0.5">
        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center mb-3 group-hover:bg-white/30 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="font-semibold text-lg">Lapor Kerusakan</h3>
        <p class="text-amber-100 text-sm mt-1">Laporkan alat yang rusak</p>
    </a>

    <a href="{{ route('schedules.index') }}" class="group bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 transition-all duration-300 hover:-translate-y-0.5">
        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center mb-3 group-hover:bg-white/30 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="font-semibold text-lg">Lihat Jadwal</h3>
        <p class="text-emerald-100 text-sm mt-1">Jadwal penggunaan laboratorium</p>
    </a>
    <a href="{{ route('laboratories.index') }}" class="group bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition-all duration-300 hover:-translate-y-0.5">
        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center mb-3 group-hover:bg-white/30 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <h3 class="font-semibold text-lg">Daftar Lab</h3>
        <p class="text-indigo-100 text-sm mt-1">Lihat daftar & fasilitas lab</p>
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- My Borrowings --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-700">Peminjaman Saya</h2>
            <a href="{{ route('borrowings.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Lihat Semua →</a>
        </div>
        <div class="p-5">
            @forelse($myBorrowings as $b)
            <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $b->laboratory->name }}</p>
                    <p class="text-xs text-slate-400">{{ $b->borrow_date->format('d/m/Y') }} · {{ \Carbon\Carbon::parse($b->start_time)->format('H:i') }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $b->status === 'pending' ? 'bg-yellow-50 text-yellow-700' : '' }}
                    {{ $b->status === 'approved' ? 'bg-blue-50 text-blue-700' : '' }}
                    {{ $b->status === 'rejected' ? 'bg-red-50 text-red-700' : '' }}
                    {{ $b->status === 'completed' ? 'bg-green-50 text-green-700' : '' }}
                    {{ $b->status === 'cancelled' ? 'bg-slate-50 text-slate-700' : '' }}
                ">{{ $b->status_label }}</span>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Belum ada peminjaman</p>
            @endforelse
        </div>
    </div>

    {{-- Today Schedules --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-700">Jadwal Hari Ini ({{ now()->translatedFormat('l, d F Y') }})</h2>
        </div>
        <div class="p-5">
            @forelse($todaySchedules as $s)
            <div class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-slate-50' : '' }}">
                <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-700 truncate">{{ $s->title }}</p>
                    <p class="text-xs text-slate-400">{{ $s->laboratory->name }} · {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($s->end_time)->format('H:i') }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-4">Tidak ada jadwal hari ini</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
