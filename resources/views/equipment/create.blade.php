@extends('layouts.app')
@section('title', 'Tambah Alat')

@section('content')
<div class="mb-6">
    <a href="{{ route('equipment.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali ke Daftar Alat</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Tambah Alat Baru</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
        <ul class="text-sm text-red-600 space-y-1">
            @foreach($errors->all() as $error)
            <li>• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('equipment.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Nama Alat *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
            </div>
            <div>
                <label for="code" class="block text-sm font-medium text-slate-700 mb-1.5">Kode Inventaris *</label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-400">
            </div>
            <div>
                <label for="laboratory_id" class="block text-sm font-medium text-slate-700 mb-1.5">Laboratorium *</label>
                <select id="laboratory_id" name="laboratory_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="">Pilih Lab</option>
                    @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}" {{ old('laboratory_id') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="category_id" class="block text-sm font-medium text-slate-700 mb-1.5">Kategori</label>
                <select id="category_id" name="category_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="brand" class="block text-sm font-medium text-slate-700 mb-1.5">Merk</label>
                <input type="text" id="brand" name="brand" value="{{ old('brand') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>
            <div>
                <label for="model" class="block text-sm font-medium text-slate-700 mb-1.5">Model</label>
                <input type="text" id="model" name="model" value="{{ old('model') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>
            <div>
                <label for="serial_number" class="block text-sm font-medium text-slate-700 mb-1.5">Serial Number</label>
                <input type="text" id="serial_number" name="serial_number" value="{{ old('serial_number') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>
            <div>
                <label for="year_acquired" class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Perolehan</label>
                <input type="number" id="year_acquired" name="year_acquired" value="{{ old('year_acquired') }}" min="2000" max="{{ date('Y') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>
            <div>
                <label for="price" class="block text-sm font-medium text-slate-700 mb-1.5">Harga (Rp)</label>
                <input type="number" id="price" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>
            <div>
                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-1.5">Jumlah *</label>
                <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
            </div>
            <div>
                <label for="condition" class="block text-sm font-medium text-slate-700 mb-1.5">Kondisi *</label>
                <select id="condition" name="condition" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="baik" {{ old('condition') == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak_ringan" {{ old('condition') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                    <option value="rusak_berat" {{ old('condition') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                    <option value="hilang" {{ old('condition') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                <select id="status" name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Tersedia</option>
                    <option value="in_use" {{ old('status') == 'in_use' ? 'selected' : '' }}>Digunakan</option>
                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>Dihapuskan</option>
                </select>
            </div>
            <div>
                <label for="photo" class="block text-sm font-medium text-slate-700 mb-1.5">Foto</label>
                <input type="file" id="photo" name="photo" accept="image/*" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30 file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700">
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi</label>
            <textarea id="description" name="description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('description') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('equipment.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Simpan</button>
        </div>
    </form>
</div>
@endsection
