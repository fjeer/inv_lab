@extends('layouts.app')
@section('title', 'Detail Pemeriksaan')
@section('content')
<div class="mb-6">
    <a href="{{ route('conditions.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Detail Pemeriksaan Kondisi</h1>
</div>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div><dt class="text-slate-400">Alat</dt><dd class="text-slate-700 font-medium mt-0.5">{{ $condition->equipment->name }}</dd></div>
        <div><dt class="text-slate-400">Kode Unik / Item</dt><dd class="text-slate-700 font-mono text-xs font-semibold mt-0.5 bg-slate-50 px-2 py-1 rounded border border-slate-100 inline-block">{{ $condition->equipmentItem?->qr_code ?? '-' }}</dd></div>
        <div><dt class="text-slate-400">Lab</dt><dd class="text-slate-700 mt-0.5">{{ $condition->equipment->laboratory->name }}</dd></div>
        <div><dt class="text-slate-400">Tanggal Periksa</dt><dd class="text-slate-700 mt-0.5">{{ $condition->check_date->format('d F Y') }}</dd></div>
        <div><dt class="text-slate-400">Pemeriksa</dt><dd class="text-slate-700 mt-0.5">{{ $condition->checker->name }}</dd></div>
        <div><dt class="text-slate-400">Kondisi Saat Ini</dt><dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $condition->condition === 'baik' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">{{ $condition->condition_label }}</span></dd></div>
        <div><dt class="text-slate-400">Kondisi Sebelumnya</dt><dd class="text-slate-700 mt-0.5">{{ $condition->previous_condition ? ucfirst(str_replace('_', ' ', $condition->previous_condition)) : '-' }}</dd></div>
    </dl>
    @if($condition->description)<div class="mt-4 pt-4 border-t border-slate-100"><dt class="text-sm text-slate-400">Deskripsi</dt><dd class="text-sm text-slate-700 mt-1">{{ $condition->description }}</dd></div>@endif
    @if($condition->action_taken)<div class="mt-4 pt-4 border-t border-slate-100"><dt class="text-sm text-slate-400">Tindakan</dt><dd class="text-sm text-slate-700 mt-1">{{ $condition->action_taken }}</dd></div>@endif
</div>
@endsection
