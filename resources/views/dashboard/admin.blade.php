@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-slate-800">Ringkasan Laboratorium</h1>
    <p class="text-slate-500 text-sm mt-1">Selamat datang kembali, {{ Auth::user()->name }}</p>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-blue-200 group-hover:shadow-blue-300 group-hover:scale-105 transition-all duration-300 icon-shine">
                <svg class="w-6 h-6 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2-2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Total Alat</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 stat-total-equipment">--</h3>
                    <span class="text-xs text-emerald-600 font-medium stat-equipment-baik">-- Baik</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Total Stok: <span class="font-semibold text-slate-600 stat-total-stok">--</span> item</p>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200 group-hover:shadow-emerald-300 group-hover:scale-105 transition-all duration-300 icon-shine">
                <svg class="w-6 h-6 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation-delay: 0.5s"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Laboratorium</p>
                <h3 class="text-2xl font-bold text-slate-800 stat-total-laboratories">--</h3>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-amber-200 group-hover:shadow-amber-300 group-hover:scale-105 transition-all duration-300 icon-shine">
                <svg class="w-6 h-6 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation-delay: 1s"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Peminjaman Aktif</p>
                <h3 class="text-2xl font-bold text-slate-800 stat-active-borrowings">--</h3>
            </div>
        </div>
    </div>
    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-red-400 to-red-600 text-white rounded-xl flex items-center justify-center shadow-lg shadow-red-200 group-hover:shadow-red-300 group-hover:scale-105 transition-all duration-300 icon-shine">
                <svg class="w-6 h-6 icon-float" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="animation-delay: 1.5s"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Laporan Kerusakan</p>
                <h3 class="text-2xl font-bold text-slate-800 stat-equipment-rusak">--</h3>
            </div>
        </div>
    </div>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    {{-- Recent Activity --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800">Aktivitas Terbaru</h3>
            <a href="#" class="text-blue-600 text-sm font-semibold hover:text-blue-700">Lihat Semua</a>
        </div>
        <div class="divide-y divide-slate-50" id="recent-activity-list">
            <div class="p-8 text-center text-slate-400">Memuat aktivitas...</div>
        </div>
    </div>

    {{-- Urgent Maintenance --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-50">
            <h3 class="font-bold text-slate-800">Laporan Kerusakan Terbaru</h3>
        </div>
        <div class="p-6" id="urgent-reports-list">
            <div class="text-center text-slate-400">Memuat laporan...</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Load Dashboard Data
    $.get('/api/dashboard/stats', function(res) {
        if(res.status === 'success') {
            const data = res.data;
            const stats = data.stats;
            
            // Update Stats
            $('.stat-total-equipment').text(stats.total_equipment);
            $('.stat-total-stok').text(stats.total_stok);
            $('.stat-equipment-baik').text(stats.equipment_baik + ' Baik');
            $('.stat-total-laboratories').text(stats.total_laboratories);
            $('.stat-active-borrowings').text(stats.active_borrowings);
            $('.stat-equipment-rusak').text(stats.equipment_rusak);

            // Render Recent Activity
            if (data.recent_activities && data.recent_activities.length > 0) {
                let activityHtml = '';
                data.recent_activities.forEach(item => {
                    const icon = getActionIcon(item.action);
                    activityHtml += `
                        <div class="p-4 flex items-center gap-4 hover:bg-slate-50 transition-colors">
                            <div class="w-10 h-10 rounded-full ${icon.bg} flex items-center justify-center ${icon.text}">
                                ${icon.svg}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-800">${item.description || item.action}</p>
                                <p class="text-xs text-slate-400">${item.user_name} • ${item.time}</p>
                            </div>
                        </div>
                    `;
                });
                $('#recent-activity-list').html(activityHtml);
            } else {
                $('#recent-activity-list').html('<div class="p-8 text-center text-slate-400">Belum ada aktivitas terbaru</div>');
            }

            // Render Damage Reports
            if (data.urgent_damage_reports && data.urgent_damage_reports.length > 0) {
                let reportsHtml = '<div class="flex flex-col gap-4">';
                data.urgent_damage_reports.forEach(report => {
                    const typeColor = report.damage_type === 'berat' ? 'red' : (report.damage_type === 'sedang' ? 'amber' : 'blue');
                    reportsHtml += `
                        <div class="p-4 rounded-xl border border-${typeColor}-50 bg-${typeColor}-50/20">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-bold text-${typeColor}-600 uppercase">${report.damage_type}</span>
                                <span class="text-xs text-slate-400">${report.date}</span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800">${report.equipment_name}</h4>
                            <p class="text-xs text-slate-500 mt-1">${report.description}</p>
                        </div>
                    `;
                });
                reportsHtml += '</div>';
                $('#urgent-reports-list').html(reportsHtml);
            } else {
                $('#urgent-reports-list').html('<div class="text-center text-slate-400 py-4">Tidak ada laporan kerusakan aktif</div>');
            }
        }
    });

    function getActionIcon(action) {
        if (action.includes('create') || action.includes('add')) {
            return {
                bg: 'bg-blue-50',
                text: 'text-blue-600',
                svg: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>'
            };
        }
        if (action.includes('approve') || action.includes('success')) {
            return {
                bg: 'bg-emerald-50',
                text: 'text-emerald-600',
                svg: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
            };
        }
        if (action.includes('delete') || action.includes('remove') || action.includes('reject')) {
            return {
                bg: 'bg-red-50',
                text: 'text-red-600',
                svg: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
            };
        }
        return {
            bg: 'bg-slate-50',
            text: 'text-slate-600',
            svg: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        };
    }
});
</script>
@endpush
@endsection
