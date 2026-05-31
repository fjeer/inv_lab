@extends('layouts.app')
@section('title', 'Detail Peminjaman')

@section('content')
<div class="mb-6">
    <a href="{{ route('borrowings.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Detail Peminjaman #{{ $borrowing->id }}</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-700">Informasi Peminjaman</h2>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold
                    {{ $borrowing->status === 'pending' ? 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200' : '' }}
                    {{ $borrowing->status === 'approved' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200' : '' }}
                    {{ $borrowing->status === 'rejected' ? 'bg-red-50 text-red-700 ring-1 ring-red-200' : '' }}
                    {{ $borrowing->status === 'completed' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : '' }}
                    {{ $borrowing->status === 'cancelled' ? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' : '' }}
                ">{{ $borrowing->status_label }}</span>
            </div>

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Peminjam</dt><dd class="text-slate-700 font-medium mt-0.5">{{ $borrowing->user->name }}</dd></div>
                <div><dt class="text-slate-400">Laboratorium</dt><dd class="text-slate-700 mt-0.5">{{ $borrowing->laboratory->name }}</dd></div>
                <div><dt class="text-slate-400">Tanggal</dt><dd class="text-slate-700 mt-0.5">{{ $borrowing->borrow_date->format('d F Y') }}</dd></div>
                <div><dt class="text-slate-400">Waktu</dt><dd class="text-slate-700 mt-0.5 font-mono">{{ \Carbon\Carbon::parse($borrowing->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($borrowing->end_time)->format('H:i') }}</dd></div>
                <div><dt class="text-slate-400">Jenis Kegiatan</dt><dd class="text-slate-700 mt-0.5">{{ $borrowing->activity_type ?? '-' }}</dd></div>
                @if($borrowing->approver)
                <div><dt class="text-slate-400">Disetujui oleh</dt><dd class="text-slate-700 mt-0.5">{{ $borrowing->approver->name }}</dd></div>
                <div><dt class="text-slate-400">Tanggal Persetujuan</dt><dd class="text-slate-700 mt-0.5">{{ $borrowing->approved_at?->format('d/m/Y H:i') }}</dd></div>
                @endif
            </dl>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <dt class="text-sm text-slate-400 font-medium">Tujuan</dt>
                <dd class="text-sm text-slate-700 mt-1">{{ $borrowing->purpose }}</dd>
            </div>

            @if($borrowing->rejection_reason)
            <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100">
                <p class="text-sm font-medium text-red-700">Alasan Penolakan:</p>
                <p class="text-sm text-red-600 mt-1">{{ $borrowing->rejection_reason }}</p>
            </div>
            @endif
        </div>


    </div>

    {{-- Action Sidebar --}}
    <div class="space-y-4">
        @if(Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten') && $borrowing->status === 'pending')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Kelola Peminjaman</h3>
            <form id="approve-form">@csrf
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-500 transition-colors">✓ Setujui</button>
            </form>
            <form id="reject-form" class="space-y-2">@csrf
                <textarea name="rejection_reason" required placeholder="Alasan penolakan..." rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500/30"></textarea>
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-500 transition-colors">✕ Tolak</button>
            </form>
        </div>
        @endif

        @if(Auth::user()->hasRole('admin_lab', 'asisten_lab', 'admin', 'asisten') && $borrowing->status === 'approved')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <form id="complete-form">@csrf
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-500 transition-colors">Selesaikan Peminjaman</button>
            </form>
        </div>
        @endif

        @if(Auth::id() === $borrowing->user_id && $borrowing->status === 'pending')
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <form id="cancel-form">@csrf
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition-colors">Batalkan Peminjaman</button>
            </form>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#approve-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/borrowings/{{ $borrowing->id }}/approve',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(err) {
                Swal.fire('Error', err.responseJSON?.message || 'Gagal menyetujui peminjaman.', 'error');
            }
        });
    });

    $('#reject-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/borrowings/{{ $borrowing->id }}/reject',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(err) {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Gagal menolak peminjaman.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });

    $('#complete-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/borrowings/{{ $borrowing->id }}/complete',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(err) {
                Swal.fire('Error', err.responseJSON?.message || 'Gagal menyelesaikan peminjaman.', 'error');
            }
        });
    });

    $('#cancel-form').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Batalkan peminjaman?',
            text: "Tindakan ini tidak dapat dibatalkan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, batalkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/api/borrowings/{{ $borrowing->id }}/cancel',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        window.showAlert('Berhasil!', res.message, 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("borrowings.index") }}';
                        }, 1500);
                    },
                    error: function(err) {
                        Swal.fire('Error', err.responseJSON?.message || 'Gagal membatalkan peminjaman.', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
@endsection
