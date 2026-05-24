@extends('layouts.app')
@section('title', 'Tambah Jadwal')

@section('content')
<div class="mb-6">
    <a href="{{ route('schedules.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Tambah Jadwal Lab</h1>
</div>

<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 max-w-2xl">
    @if($errors->any())<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl"><ul class="text-sm text-red-600 space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul></div>@endif

    <form id="schedule-form" class="space-y-5">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Mata Kuliah / Kegiatan *</label><input type="text" name="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Lab *</label>
                <select name="laboratory_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"><option value="">Pilih</option>@foreach($laboratories as $l)<option value="{{ $l->id }}" {{ old('laboratory_id') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Hari *</label>
                <select name="day_of_week" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    @foreach(['monday'=>'Senin','tuesday'=>'Selasa','wednesday'=>'Rabu','thursday'=>'Kamis','friday'=>'Jumat','saturday'=>'Sabtu','sunday'=>'Minggu'] as $v => $l)
                    <option value="{{ $v }}" {{ old('day_of_week') == $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach
                </select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Dosen / PJ</label>
                <select name="user_id" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"><option value="">-</option>@foreach($dosens as $d)<option value="{{ $d->id }}" {{ old('user_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Mulai *</label><input type="time" name="start_time" value="{{ old('start_time') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jam Selesai *</label><input type="time" name="end_time" value="{{ old('end_time') }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Semester</label><input type="text" name="semester" value="{{ old('semester') }}" placeholder="Gasal 2025/2026" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Akademik</label><input type="text" name="academic_year" value="{{ old('academic_year') }}" placeholder="2025/2026" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas / Kelompok</label><input type="text" name="class_group" value="{{ old('class_group') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
            <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Status *</label>
                <select name="status" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <option value="active">Aktif</option><option value="inactive">Nonaktif</option>
                </select></div>
        </div>
        <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label><textarea name="notes" rows="2" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">{{ old('notes') }}</textarea></div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('schedules.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Simpan</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#schedule-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/schedules',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("schedules.index") }}';
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
