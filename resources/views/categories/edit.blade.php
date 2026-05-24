@extends('layouts.app')
@section('title', 'Edit Kategori')
@section('content')
<div class="mb-6"><a href="{{ route('categories.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a><h1 class="text-2xl font-bold text-slate-800 mt-2">Edit: {{ $category->name }}</h1></div>
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-lg">
    @if($errors->any())<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-5">@csrf @method('PUT')
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama *</label><input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label><textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('description', $category->description) }}</textarea></div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('categories.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Perbarui</button>
        </div>
    </form>
</div>
@endsection
