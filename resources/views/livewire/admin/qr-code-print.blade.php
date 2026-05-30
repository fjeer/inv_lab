@section('title', 'Cetak QR Code')

<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Cetak QR Code Alat Lab</h1>
        <p class="text-sm text-slate-500 mt-1">Pilih laboratorium dan alat untuk mencetak QR code secara massal.</p>
    </div>

    {{-- Flash messages --}}
    @if(session()->has('error'))
    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Pilih Laboratorium</label>
                <select wire:model.live="selectedLabId" class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Lab --</option>
                    @foreach($laboratories as $lab)
                    <option value="{{ $lab->id }}">{{ $lab->name }} ({{ $lab->code }})</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button wire:click="loadSelectedItems" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Siapkan Cetak
                </button>
            </div>
        </div>
    </div>

    {{-- Equipment List --}}
    @if(count($equipmentList) > 0)
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">Pilih Alat</h2>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                <span class="text-sm font-medium text-slate-600">Pilih Semua</span>
            </label>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($equipmentList as $eq)
            <div class="p-4" wire:key="eq-{{ $eq->id }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <button wire:click="selectAllFromEquipment({{ $eq->id }})" class="text-xs px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 font-medium transition-colors">Pilih Semua</button>
                        <span class="font-semibold text-slate-700">{{ $eq->name }}</span>
                        <span class="text-xs text-slate-400">({{ $eq->items->count() }} item)</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                    @foreach($eq->items as $item)
                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-200 hover:border-blue-300 hover:bg-blue-50/50 cursor-pointer transition-all" wire:key="item-{{ $item->id }}">
                        <input type="checkbox" wire:model="selectedItems" value="{{ $item->id }}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
                        <span class="text-xs font-mono text-slate-600 truncate">{{ $item->qr_code }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Print Preview --}}
    @if(count($items) > 0)
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden" id="print-area">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between no-print">
            <h2 class="text-lg font-bold text-slate-800">Preview Cetak ({{ count($items) }} QR Code)</h2>
            <button onclick="window.print()" class="px-4 py-2 bg-emerald-500 text-white text-sm font-semibold rounded-xl hover:bg-emerald-600 transition-colors">
                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Sekarang
            </button>
        </div>
        <div class="p-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($items as $item)
            <div class="border border-slate-200 rounded-xl p-4 text-center" wire:key="print-{{ $item->id }}">
                <div class="mx-auto mb-3 flex items-center justify-center" style="width:120px;height:120px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($item->qr_code) }}" alt="QR Code" class="w-24 h-24 sm:w-28 sm:h-28 object-contain">
                </div>
                <p class="text-xs font-mono text-slate-600 leading-tight break-all">{{ $item->qr_code }}</p>
                <p class="text-[10px] text-slate-400 mt-1">{{ $item->equipment->name }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@push('scripts')
<script>
// JavaScript qrcode library is bypassed in favor of instant static url rendering for ultra-reliability during Livewire DOM changes
</script>
<style>
@media print {
    /* Hide layout elements completely */
    body, html {
        background: #ffffff !important;
        color: #000000 !important;
    }
    nav, aside, #sidebar, #sidebar-overlay, .no-print, header, footer { 
        display: none !important; 
    }
    main { 
        margin-left: 0 !important; 
        padding: 0 !important; 
        min-height: 0 !important;
    }
    .space-y-6 > div:not(#print-area) {
        display: none !important;
    }
    
    /* Print container style */
    #print-area {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    #print-area .p-5.grid {
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 15px !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    #print-area .border {
        border: 1px dashed #cbd5e1 !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
}
</style>
@endpush
