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
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h2 class="font-semibold text-slate-700 mb-4">🔗 Telegram</h2>

            @if($user->hasTelegramLinked())
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 rounded-full font-semibold bg-green-50 text-green-700">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Terhubung ke Telegram
                    </span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" id="unlink-telegram"
                        class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 transition-colors">
                        Putuskan
                    </button>

                    @if($user->isAdmin())
                        <button type="button" id="test-telegram"
                            class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-100 rounded-xl hover:bg-blue-100 transition-colors">
                            📨 Kirim Pesan Uji Coba
                        </button>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-500 mb-4">
                    Hubungkan akun Telegram Anda untuk menerima notifikasi patrol secara otomatis.
                </p>
                <button type="button" id="link-telegram"
                    class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">
                    🔗 Hubungkan ke Telegram
                </button>
            @endif
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

    // Telegram Link
    $('#link-telegram').on('click', function() {
        $.ajax({
            url: '/profile/link-telegram',
            type: 'POST',
            success: function(res) {
                window.open(res.data.url, '_blank');
                window.showAlert('Berhasil!', 'Token telah dibuat. Klik link di tab baru untuk menghubungkan Telegram.', 'success');
            },
            error: function(err) {
                Swal.fire('Error', err.responseJSON?.message || 'Gagal membuat token.', 'error');
            }
        });
    });

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
