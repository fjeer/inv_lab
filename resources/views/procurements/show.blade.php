@extends('layouts.app')
@section('title', 'Detail Pengadaan')

@section('content')
<div class="mb-6">
    <a href="{{ route('procurements.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">← Kembali</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">{{ $procurement->title }}</h1>
    <p class="text-sm text-slate-400 font-mono mt-0.5">{{ $procurement->procurement_number }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-slate-700">Informasi Pengadaan</h2>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold ring-1
                    {{ $procurement->status === 'submitted' ? 'bg-yellow-50 text-yellow-700 ring-yellow-200' : '' }}
                    {{ $procurement->status === 'approved' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : '' }}
                    {{ $procurement->status === 'rejected' ? 'bg-red-50 text-red-700 ring-red-200' : '' }}
                    {{ $procurement->status === 'in_review' ? 'bg-blue-50 text-blue-700 ring-blue-200' : '' }}
                    {{ $procurement->status === 'ordered' ? 'bg-indigo-50 text-indigo-700 ring-indigo-200' : '' }}
                    {{ $procurement->status === 'received' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : '' }}
                ">{{ $procurement->status_label }}</span>
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-slate-400">Pengaju</dt><dd class="text-slate-700 mt-0.5">{{ $procurement->requester->name }}</dd></div>
                <div><dt class="text-slate-400">Prioritas</dt><dd class="mt-0.5"><span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $procurement->priority === 'urgent' ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700' }}">{{ $procurement->priority_label }}</span></dd></div>
                <div><dt class="text-slate-400">Total Estimasi</dt><dd class="text-slate-700 font-semibold mt-0.5">Rp {{ number_format($procurement->total_estimated_cost, 0, ',', '.') }}</dd></div>
                <div><dt class="text-slate-400">Tanggal Pengajuan</dt><dd class="text-slate-700 mt-0.5">{{ $procurement->created_at->format('d F Y') }}</dd></div>
                @if($procurement->approver)<div><dt class="text-slate-400">Disetujui oleh</dt><dd class="text-slate-700 mt-0.5">{{ $procurement->approver->name }}</dd></div>@endif
            </dl>
            @if($procurement->description)<div class="mt-4 pt-4 border-t border-slate-100"><p class="text-sm text-slate-600">{{ $procurement->description }}</p></div>@endif
            @if($procurement->rejection_reason)
            <div class="mt-4 p-4 bg-red-50 rounded-xl border border-red-100"><p class="text-sm font-medium text-red-700">Alasan Penolakan:</p><p class="text-sm text-red-600 mt-1">{{ $procurement->rejection_reason }}</p></div>
            @endif
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-700">Daftar Item ({{ $procurement->items->count() }})</h2></div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="bg-slate-50/50">
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Nama</th>
                        <th class="text-left px-5 py-3 font-semibold text-slate-600">Spesifikasi</th>
                        <th class="text-center px-5 py-3 font-semibold text-slate-600">Qty</th>
                        <th class="text-center px-5 py-3 font-semibold text-slate-600">Satuan</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-600">Harga</th>
                        <th class="text-right px-5 py-3 font-semibold text-slate-600">Subtotal</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($procurement->items as $item)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-700">{{ $item->item_name }}</td>
                            <td class="px-5 py-3 text-slate-600 text-xs">{{ $item->specification ?? '-' }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $item->quantity }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $item->unit }}</td>
                            <td class="px-5 py-3 text-right text-slate-600">Rp {{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-medium text-slate-700">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr class="bg-slate-50/50"><td colspan="5" class="px-5 py-3 text-right font-semibold text-slate-700">Total</td><td class="px-5 py-3 text-right font-bold text-slate-800">Rp {{ number_format($procurement->total_estimated_cost, 0, ',', '.') }}</td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Admin Actions --}}
    @if(Auth::user()->isAdmin() && in_array($procurement->status, ['submitted', 'in_review']))
    <div class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
            <h3 class="font-semibold text-slate-700 text-sm">Kelola Pengadaan</h3>
            <form id="approve-form">@csrf
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-500 transition-colors">✓ Setujui</button>
            </form>
            <form id="reject-form" class="space-y-2">@csrf
                <textarea name="rejection_reason" required placeholder="Alasan penolakan..." rows="2" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-red-500/30"></textarea>
                <button type="submit" class="w-full py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-500 transition-colors">✕ Tolak</button>
            </form>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#approve-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/procurements/{{ $procurement->id }}/approve',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(err) {
                Swal.fire('Error', err.responseJSON?.message || 'Gagal menyetujui pengadaan.', 'error');
            }
        });
    });

    $('#reject-form').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: '/api/procurements/{{ $procurement->id }}/reject',
            type: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                window.showAlert('Berhasil!', res.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            },
            error: function(err) {
                const msg = err.responseJSON?.errors ? Object.values(err.responseJSON.errors).flat().join('<br>') : 'Gagal menolak pengadaan.';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
@endpush
@endsection
