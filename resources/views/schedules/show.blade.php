@extends('layouts.app')
@section('title', 'Detail Jadwal')

@section('content')
<div class="mb-6">
    <a href="{{ route('schedules.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">{{ $schedule->title }}</h1>
</div>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-slate-400">Lab</dt><dd class="text-slate-700 mt-0.5">{{ $schedule->laboratory->name }}</dd></div>
        <div><dt class="text-slate-400">Hari</dt><dd class="text-slate-700 mt-0.5">{{ $schedule->day_label }}</dd></div>
        <div><dt class="text-slate-400">Waktu</dt><dd class="text-slate-700 mt-0.5 font-mono">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</dd></div>
        <div><dt class="text-slate-400">Dosen / PJ</dt><dd class="text-slate-700 mt-0.5">{{ $schedule->user?->name ?? '-' }}</dd></div>
        <div><dt class="text-slate-400">Semester</dt><dd class="text-slate-700 mt-0.5">{{ $schedule->semester ?? '-' }}</dd></div>
        <div><dt class="text-slate-400">Kelas</dt><dd class="text-slate-700 mt-0.5">{{ $schedule->class_group ?? '-' }}</dd></div>
        <div><dt class="text-slate-400">Status</dt><dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $schedule->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($schedule->status) }}</span></dd></div>
    </dl>
    @if($schedule->notes)<p class="text-sm text-slate-600 mt-4 pt-4 border-t border-slate-100">{{ $schedule->notes }}</p>@endif
</div>
@endsection
