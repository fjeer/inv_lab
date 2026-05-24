@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('content')
<div class="mb-6"><a href="{{ route('users.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a><h1 class="text-2xl font-bold text-slate-800 mt-2">Tambah Pengguna</h1></div>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    @if($errors->any())<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('users.store') }}" class="space-y-5">@csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama *</label><input type="text" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Email *</label><input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Password *</label><input type="password" name="password" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Role *</label><select name="role" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"><option value="pengguna" {{ old('role') == 'pengguna' ? 'selected' : '' }}>Pengguna</option><option value="asisten_lab" {{ old('role') == 'asisten_lab' ? 'selected' : '' }}>Asisten Lab</option><option value="admin_lab" {{ old('role') == 'admin_lab' ? 'selected' : '' }}>Admin Lab</option></select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">NIM / NIP</label><input type="text" name="nim_nip" value="{{ old('nim_nip') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP</label><input type="text" name="phone" value="{{ old('phone') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jurusan / Prodi</label><input type="text" name="department" value="{{ old('department') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div class="flex items-center gap-2 pt-6"><input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/50"><label class="text-sm text-slate-700">Aktif</label></div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Simpan</button>
        </div>
    </form>
</div>
@endsection
