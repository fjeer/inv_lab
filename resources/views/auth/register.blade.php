@extends('layouts.auth')
@section('title', 'Daftar')

@section('content')
<div class="bg-white/5 backdrop-blur-2xl rounded-3xl border border-white/10 p-8 shadow-2xl">
    {{-- Logo --}}
    <div class="text-center mb-6">
        <div class="mx-auto w-14 h-14 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/30 mb-3">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-white">Buat Akun Baru</h1>
        <p class="text-blue-200/60 text-xs mt-1">Daftar sebagai anggota laboratorium</p>
    </div>

    <form id="register-form" class="space-y-3">
        <div>
            <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">Nama Lengkap</label>
            <input type="text" name="name" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
        </div>

        <div>
            <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">Email</label>
            <input type="email" name="email" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">NIM / NIP</label>
                <input type="text" name="nim_nip" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">No. HP</label>
                <input type="text" name="phone" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            </div>
        </div>

        <div>
            <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">Jurusan / Prodi</label>
            <input type="text" name="department" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-blue-100/60 uppercase tracking-wider mb-1">Konfirmasi</label>
                <input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50">
            </div>
        </div>

        <button type="submit" id="btn-register" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl shadow-lg mt-4 transition-all active:scale-[0.98]">
            Daftar Sekarang
        </button>
    </form>

    <p class="text-center mt-6 text-sm text-blue-100/40 font-medium">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="text-blue-400 hover:text-blue-300 font-bold transition-colors">Masuk</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#register-form').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-register');
        const originalText = btn.text();
        btn.prop('disabled', true).text('Memproses Akun...');

        $.ajax({
            url: '/api/register',
            type: 'POST',
            data: $(this).serialize(),
            success: (res) => {
                // Store token
                localStorage.setItem('access_token', res.data.access_token);

                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: 'Akun Anda telah aktif.',
                    showConfirmButton: false, timer: 1500
                }).then(() => window.location.href = res.data.redirect);
            },
            error: (err) => {
                btn.prop('disabled', false).text(originalText);
                const errors = err.responseJSON?.errors;
                let msg = 'Gagal mendaftar.';
                if (errors) msg = Object.values(errors).flat().join('<br>');
                Swal.fire({ icon: 'error', title: 'Pendaftaran Gagal', html: msg });
            }
        });
    });
});
</script>
@endpush
