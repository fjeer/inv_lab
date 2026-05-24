@extends('layouts.app')
@section('title', 'Jadwal Peminjaman')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Jadwal Peminjaman</h1>
    <p class="text-slate-500 text-sm mt-1">Daftar peminjaman yang sudah disetujui / berlangsung</p>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-col sm:flex-row gap-3">
        <select name="laboratory_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            <option value="">Semua Lab</option>
            @foreach($laboratories as $lab)<option value="{{ $lab->id }}" {{ request('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>@endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-xl hover:bg-slate-700 transition-colors">Filter</button>
    </form>
</div>

<div class="space-y-3">
    @forelse($borrowings as $b)
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md transition-shadow flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="shrink-0 text-center bg-gradient-to-br from-blue-500 to-indigo-600 text-white rounded-xl px-4 py-3 min-w-20 shadow-md shadow-blue-500/20">
            <p class="text-2xl font-bold leading-none">{{ $b->borrow_date->format('d') }}</p>
            <p class="text-xs font-medium uppercase tracking-wider mt-0.5 text-blue-100">{{ $b->borrow_date->format('M Y') }}</p>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-slate-800">{{ $b->laboratory->name }}</h3>
            <p class="text-sm text-slate-500 mt-0.5">{{ $b->user->name }} · {{ $b->activity_type ?? 'Peminjaman' }}</p>
            <p class="text-xs text-slate-400 font-mono mt-1">{{ \Carbon\Carbon::parse($b->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($b->end_time)->format('H:i') }}</p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium shrink-0 {{ $b->status === 'approved' ? 'bg-blue-50 text-blue-700' : 'bg-indigo-50 text-indigo-700' }}">{{ $b->status_label }}</span>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-10 text-center text-slate-400">
        Belum ada jadwal peminjaman
    </div>
    @endforelse
</div>
@endsection
