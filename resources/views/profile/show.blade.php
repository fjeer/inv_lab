@extends('layouts.app')
@section('title', 'Profil Saya')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Profil Saya</h1>
    <p class="text-slate-500 text-sm mt-1">Kelola informasi profil dan ubah password</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Profile Card --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 text-center">
        <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-3xl font-bold shadow-xl shadow-blue-500/25 mb-4">
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="w-full h-full rounded-full object-cover">
            @else
                {{ strtoupper(substr($user->name, 0, 2)) }}
            @endif
        </div>
        <h2 class="text-lg font-bold text-slate-800">{{ $user->name }}</h2>
        <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}</p>
        <span class="inline-block mt-2 text-xs px-3 py-1 rounded-full font-semibold {{ $user->role === 'admin_lab' ? 'bg-purple-50 text-purple-700' : ($user->role === 'asisten_lab' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-600') }}">{{ $user->role_label }}</span>
        <div class="mt-4 pt-4 border-t border-slate-100 text-sm text-slate-500 space-y-1.5">
            @if($user->nim_nip)<p>NIM/NIP: <span class="text-slate-700 font-mono">{{ $user->nim_nip }}</span></p>@endif
            @if($user->phone)<p>HP: <span class="text-slate-700">{{ $user->phone }}</span></p>@endif
            @if($user->department)<p>Jurusan: <span class="text-slate-700">{{ $user->department }}</span></p>@endif
        </div>
    </div>

    <div class="lg:col-span-2 space-y-6">
        {{-- Update Profile --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-semibold text-slate-700 mb-4">Perbarui Profil</h2>
            <form id="profile-form" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Nama *</label><input type="text" name="name" value="{{ $user->name }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Email *</label><input type="email" name="email" value="{{ $user->email }}" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">NIM / NIP</label><input type="text" name="nim_nip" value="{{ $user->nim_nip }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">No. HP</label><input type="text" name="phone" value="{{ $user->phone }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Jurusan</label><input type="text" name="department" value="{{ $user->department }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Avatar</label><input type="file" name="avatar" accept="image/*" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700"></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">Simpan Profil</button></div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-semibold text-slate-700 mb-4">Ubah Password</h2>
            <form id="password-form" class="space-y-4">
                @csrf
                <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Password Saat Ini</label><input type="password" name="current_password" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Password Baru</label><input type="password" name="password" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                    <div><label class="block text-sm font-medium text-slate-700 mb-1.5">Konfirmasi Password</label><input type="password" name="password_confirmation" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30"></div>
                </div>
                <div class="flex justify-end"><button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-slate-800 rounded-xl hover:bg-slate-700 transition-colors">Ubah Password</button></div>
            </form>
        </div>

        {{-- Telegram Integration --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden" id="telegram-section">
            {{-- Premium Header with Telegram branding --}}
            <div class="bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center icon-shine">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white text-lg">Notifikasi Telegram</h2>
                        <p class="text-blue-100 text-xs">Terima pemberitahuan patrol secara real-time</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
            @if($user->hasTelegramLinked())
                {{-- Connected State --}}
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-sm shadow-emerald-300"></span>
                                <span class="text-sm font-semibold text-slate-700">Terhubung</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">Notifikasi patrol akan dikirim ke Telegram Anda</p>
                        </div>
                    </div>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Active
                    </span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" id="unlink-telegram"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 hover:border-red-200 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Putuskan Koneksi
                    </button>
                    @if($user->isAdmin())
                        <button type="button" id="test-telegram"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-100 rounded-xl hover:bg-blue-100 hover:border-blue-200 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            Kirim Pesan Uji Coba
                        </button>
                    @endif
                </div>
            @else
                {{-- Not Connected State --}}
                <div id="telegram-not-linked">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-slate-700 mb-1">Hubungkan Telegram</h3>
                            <p class="text-sm text-slate-500 leading-relaxed">
                                Dapatkan notifikasi patrol harian dan laporan langsung ke Telegram Anda.
                            </p>
                            <ul class="mt-3 space-y-2">
                                <li class="flex items-center gap-2.5 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Pengingat jadwal patrol setiap hari
                                </li>
                                <li class="flex items-center gap-2.5 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Laporan ringkasan untuk admin
                                </li>
                                <li class="flex items-center gap-2.5 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Notifikasi real-time
                                </li>
                            </ul>
                        </div>
                    </div>
                    <button type="button" id="link-telegram"
                        class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                        Hubungkan ke Telegram
                    </button>
                </div>

                {{-- Polling State --}}
                <div id="telegram-polling" class="hidden">
                    <div class="flex flex-col items-center py-4">
                        <div class="relative mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-300">
                                <svg class="w-8 h-8 text-white animate-bounce" viewBox="0 0 24 24" fill="currentColor" style="animation-duration: 1.5s">
                                    <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                                </svg>
                            </div>
                            <div class="absolute -top-1 -right-1">
                                <span class="flex h-5 w-5">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-5 w-5 bg-blue-500"></span>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 text-sm font-medium text-slate-700 mb-2">
                            <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span id="polling-status">Menunggu konfirmasi dari Telegram...</span>
                        </div>
                        <p class="text-xs text-slate-400 text-center max-w-sm">
                            Kirim pesan <code class="text-xs bg-slate-100 px-1.5 py-0.5 rounded font-mono text-blue-600">/start</code> ke bot Telegram yang sudah terbuka di tab baru.
                        </p>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Handle Profile Form Submit
    $('#profile-form').on('submit', function(e) {
        e.preventDefault();
        
        // Since we are uploading a file (avatar), we must use FormData
        const formData = new FormData(this);
        // Emulate PUT method for Laravel API route
        formData.append('_method', 'PUT');

        $.ajax({
            url: '/api/profile',
            type: 'POST', // POST with _method=PUT to support file uploads
            data: formData,
            processData: false,
            contentType: false,
            success: (res) => {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            },
            error: (err) => {
                const errors = err.responseJSON?.errors;
                let msg = err.responseJSON?.message || 'Gagal memperbarui profil.';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Handle Password Form Submit
    $('#password-form').on('submit', function(e) {
        e.preventDefault();
        
        const data = $(this).serialize() + '&_method=PUT';

        $.ajax({
            url: '/api/profile/password',
            type: 'POST', // Emulate PUT
            data: data,
            success: (res) => {
                window.showAlert('Berhasil!', res.message, 'success');
                $('#password-form')[0].reset();
            },
            error: (err) => {
                const errors = err.responseJSON?.errors;
                let msg = err.responseJSON?.message || 'Gagal mengubah password.';
                if (errors) {
                    msg = Object.values(errors).flat().join('<br>');
                }
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    // Telegram Link + Auto Polling
    let telegramPollTimer = null;

    $('#link-telegram').on('click', function() {
        $.ajax({
            url: '/profile/link-telegram',
            type: 'POST',
            success: function(res) {
                window.open(res.data.url, '_blank');
                $('#telegram-not-linked').addClass('hidden');
                $('#telegram-polling').removeClass('hidden');
                $('#telegram-polling .animate-spin').removeClass('hidden');
                startTelegramPoll();
            },
            error: function(err) {
                Swal.fire('Error', err.responseJSON?.message || 'Gagal membuat token.', 'error');
            }
        });
    });

    function startTelegramPoll() {
        let attempts = 0;
        const maxAttempts = 30;

        telegramPollTimer = setInterval(function() {
            attempts++;
            $('#polling-status').text('Menunggu konfirmasi... (' + attempts + 's)');

            $.ajax({
                url: '/profile/check-telegram-link',
                type: 'POST',
                success: function(res) {
                    if (res.linked) {
                        clearInterval(telegramPollTimer);
                        window.showAlert('Berhasil!', 'Telegram berhasil dihubungkan!', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    }
                },
                error: function(err) {
                    $('#polling-status').text('Gagal memeriksa. Mencoba lagi...');
                }
            });

            if (attempts >= maxAttempts) {
                clearInterval(telegramPollTimer);
                $('#telegram-polling .animate-spin').addClass('hidden');
                $('#polling-status').html('⏱ Waktu habis. <a href="#" id="retry-link" class="text-blue-600 underline">Coba lagi</a>');
                $('#retry-link').on('click', function(e) {
                    e.preventDefault();
                    $('#telegram-not-linked').removeClass('hidden');
                    $('#telegram-polling').addClass('hidden');
                });
            }
        }, 3000);
    }

    // Telegram Unlink
    $('#unlink-telegram').on('click', function() {
        Swal.fire({
            title: 'Putuskan Telegram?',
            text: 'Anda tidak akan menerima notifikasi lagi.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, putuskan',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/profile/unlink-telegram',
                    type: 'POST',
                    success: function(res) {
                        window.showAlert('Berhasil!', res.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    },
                    error: function(err) {
                        Swal.fire('Error', err.responseJSON?.message || 'Gagal memutuskan.', 'error');
                    }
                });
            }
        });
    });

    // Test Telegram (Admin only)
    $('#test-telegram').on('click', function() {
        $.ajax({
            url: '/profile/test-telegram',
            type: 'POST',
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
            },
            error: function(err) {
                Swal.fire('Error', err.responseJSON?.message || 'Gagal mengirim pesan uji coba.', 'error');
            }
        });
    });
});
</script>
@endpush
@endsection
