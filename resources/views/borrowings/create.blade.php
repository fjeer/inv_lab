@extends('layouts.app')
@section('title', 'Ajukan Peminjaman')

@section('content')
<div class="mb-6">
    <a href="{{ route('borrowings.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Ajukan Peminjaman Lab</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-3xl">
    @if($errors->any())<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>@endif

    <form id="borrow-form" class="space-y-6">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Laboratorium *</label>
                <select name="laboratory_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"><option value="">Pilih Lab</option>@foreach($laboratories as $l)<option value="{{ $l->id }}" {{ old('laboratory_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jenis Kegiatan</label>
                <input type="text" name="activity_type" value="{{ old('activity_type') }}" placeholder="Praktikum, Penelitian, Workshop..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tanggal Peminjaman *</label>
                <input type="date" name="borrow_date" value="{{ old('borrow_date') }}" required min="{{ date('Y-m-d') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Mulai *</label>
                <input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Selesai *</label>
                <input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
        </div>

        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tujuan Peminjaman *</label>
            <textarea name="purpose" rows="3" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30" placeholder="Jelaskan tujuan peminjaman laboratorium...">{{ old('purpose') }}</textarea></div>

        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
            <textarea name="notes" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('notes') }}</textarea></div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('borrowings.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Ajukan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#borrow-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/borrowings',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("borrowings.index") }}';
                }, 1500);
            },
            error: function(err) {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Gagal memproses data.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
@endpush
@endsection
