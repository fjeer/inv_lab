@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="bg-white/5 backdrop-blur-2xl rounded-3xl border border-white/10 p-8 shadow-2xl">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-blue-500/30 mb-4">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-white">Selamat Datang</h1>
        <p class="text-blue-200/60 text-sm mt-1">Sistem Inventaris Laboratorium Kampus</p>
    </div>

    <form id="login-form" class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-blue-100/80 mb-1.5">Email</label>
            <input type="email" id="email" name="email" required autofocus
                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                placeholder="email@kampus.ac.id">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-blue-100/80 mb-1.5">Password</label>
            <input type="password" id="password" name="password" required
                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                placeholder="••••••••">
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500">
                <span class="text-sm text-blue-100/60 font-medium">Ingat saya</span>
            </label>
        </div>

        <button type="submit" id="btn-login" class="w-full py-4 text-sm bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-blue-500/40 transition-all active:scale-[0.98]">
            Masuk
        </button>
    </form>

    <p class="text-center mt-8 text-sm text-blue-100/40">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-blue-400 hover:text-blue-300 font-bold transition-colors">Daftar sekarang</a>
    </p>
</div>

<div class="text-center mt-10 text-[10px] tracking-widest uppercase font-bold text-blue-200/20">
    &copy; {{ date('Y') }} InvLab
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btn-login');
        const originalText = btn.text();
        btn.prop('disabled', true).text('Sedang Masuk...');

        $.ajax({
            url: '/api/login',
            type: 'POST',
            data: $(this).serialize(),
            success: (res) => {
                // Store token for external API calls/testing
                localStorage.setItem('access_token', res.data.access_token);
                
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: 'Selamat datang kembali.',
                    showConfirmButton: false, timer: 1000
                }).then(() => window.location.href = res.data.redirect);
            },
            error: (err) => {
                btn.prop('disabled', false).text(originalText);
                Swal.fire({ icon: 'error', title: 'Gagal', text: err.responseJSON?.message || 'Email atau password salah.' });
            }
        });
    });
});
</script>
@endpush
