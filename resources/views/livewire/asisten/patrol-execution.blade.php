@section('title', 'Patroli Lab')

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Patroli Lab</h1>
            @if($schedule)
            <p class="text-sm text-slate-500 mt-1">
                <span class="font-semibold text-blue-600">{{ $schedule->laboratory->name }}</span> — {{ $schedule->day_label }}, {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
            </p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-xs text-slate-400">Progress Hari Ini</p>
                <p class="text-lg font-bold text-slate-800">{{ count($scannedLogs) }} / {{ $totalItemsInLab }}</p>
            </div>
            <div class="w-12 h-12 rounded-full flex items-center justify-center {{ count($scannedLogs) >= $totalItemsInLab && $totalItemsInLab > 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-blue-100 text-blue-600' }}">
                @if(count($scannedLogs) >= $totalItemsInLab && $totalItemsInLab > 0)
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @else
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                @endif
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session()->has('message'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm flex items-center gap-2">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('message') }}
    </div>
    @endif

    {{-- Scanner Section --}}
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">Scan QR Code Alat</h2>
            <p class="text-sm text-slate-500 mt-1">Arahkan kamera ke QR code alat lab untuk memindai dan memperbarui kondisi.</p>
        </div>
        <div class="p-5">
            {{-- QR Scanner Container --}}
            <div class="flex flex-col items-center gap-4">
                <div id="qr-reader" class="w-full max-w-sm rounded-xl overflow-hidden border-2 border-dashed border-slate-300" style="min-height:300px;"></div>

                <button id="start-scan-btn" onclick="startScanner()" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Mulai Scan
                </button>

                <button id="stop-scan-btn" onclick="stopScanner()" class="hidden px-6 py-3 bg-red-500 text-white text-sm font-semibold rounded-xl shadow-lg hover:bg-red-600 transition-all">
                    Berhenti Scan
                </button>
            </div>

            {{-- Scan Error --}}
            @if($scanError)
            <div class="mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $scanError }}
            </div>
            @endif

            {{-- Condition Form (triggered after scan) --}}
            @if($showConditionForm && $scannedItem)
            <div class="mt-6 p-5 bg-blue-50 border border-blue-200 rounded-xl">
                <h3 class="text-base font-bold text-slate-800 mb-1">Alat Terdeteksi</h3>
                <p class="text-sm text-slate-600 mb-4">
                    <span class="font-semibold">{{ $scannedItem['equipment_name'] }}</span>
                    <span class="text-slate-400 ml-1 font-mono text-xs">({{ $scannedItem['qr_code'] }})</span>
                </p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Kondisi Barang</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach([
                                'baik' => ['Baik', 'bg-emerald-100 text-emerald-700 border-emerald-300', 'bg-emerald-500 text-white border-emerald-500'],
                                'rusak_ringan' => ['Rusak Ringan', 'bg-amber-100 text-amber-700 border-amber-300', 'bg-amber-500 text-white border-amber-500'],
                                'rusak_berat' => ['Rusak Berat', 'bg-red-100 text-red-700 border-red-300', 'bg-red-500 text-white border-red-500'],
                                'hilang' => ['Hilang', 'bg-slate-100 text-slate-700 border-slate-300', 'bg-slate-600 text-white border-slate-600'],
                            ] as $value => [$label, $inactiveClass, $activeClass])
                            <button wire:click="$set('selectedCondition', '{{ $value }}')"
                                class="px-4 py-2.5 rounded-xl text-sm font-semibold border-2 transition-all duration-200 {{ $selectedCondition === $value ? $activeClass : $inactiveClass }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                        @error('selectedCondition') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Catatan (Opsional)</label>
                        <textarea wire:model="conditionNotes" rows="2" class="w-full px-4 py-2.5 text-sm border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Catatan tambahan tentang kondisi alat..."></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button wire:click="submitCondition" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all">
                            <span wire:loading.remove wire:target="submitCondition">Simpan Kondisi</span>
                            <span wire:loading wire:target="submitCondition">Menyimpan...</span>
                        </button>
                        <button wire:click="cancelScan" class="px-5 py-2.5 bg-slate-200 text-slate-600 text-sm font-semibold rounded-xl hover:bg-slate-300 transition-colors">Batal</button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Today's Scanned Logs --}}
    @if(count($scannedLogs) > 0)
    <div class="bg-white rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">Riwayat Scan Hari Ini</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-5 py-3 font-semibold text-slate-600">Waktu</th>
                        <th class="px-5 py-3 font-semibold text-slate-600">Alat</th>
                        <th class="px-5 py-3 font-semibold text-slate-600">Kondisi</th>
                        <th class="px-5 py-3 font-semibold text-slate-600">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($scannedLogs as $log)
                    <tr class="hover:bg-slate-50/50 transition-colors" wire:key="log-{{ $log['id'] }}">
                        <td class="px-5 py-3 text-slate-600 text-xs">{{ \Carbon\Carbon::parse($log['checked_at'])->format('H:i:s') }}</td>
                        <td class="px-5 py-3 text-slate-700 font-medium">{{ $log['equipment_item']['equipment']['name'] ?? '-' }} <span class="text-xs text-slate-400 font-mono">#{{ $log['equipment_item']['sequence_number'] ?? '' }}</span></td>
                        <td class="px-5 py-3">
                            @php $c = $log['condition']; @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ match($c) { 'baik' => 'bg-emerald-100 text-emerald-700', 'rusak_ringan' => 'bg-amber-100 text-amber-700', 'rusak_berat' => 'bg-red-100 text-red-700', 'hilang' => 'bg-slate-100 text-slate-600', default => 'bg-slate-100 text-slate-600' } }}">
                                {{ match($c) { 'baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang', default => $c } }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-slate-500 text-xs">{{ $log['notes'] ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
let html5QrCode = null;

function startScanner() {
    const qrReaderEl = document.getElementById('qr-reader');
    if (!qrReaderEl) return;

    html5QrCode = new Html5Qrcode("qr-reader");

    document.getElementById('start-scan-btn').classList.add('hidden');
    document.getElementById('stop-scan-btn').classList.remove('hidden');

    html5QrCode.start(
        { facingMode: "environment" },
        { fps: 10, qrbox: { width: 250, height: 250 } },
        (decodedText) => {
            // Stop scanning after successful read
            stopScanner();
            @this.call('onQrScanned', decodedText);
        },
        (errorMessage) => {
            // Scanning in progress...
        }
    ).catch(err => {
        console.error("Unable to start QR scanner:", err);
        document.getElementById('start-scan-btn').classList.remove('hidden');
        document.getElementById('stop-scan-btn').classList.add('hidden');
    });
}

function stopScanner() {
    if (html5QrCode && html5QrCode.isScanning) {
        html5QrCode.stop().then(() => {
            document.getElementById('start-scan-btn').classList.remove('hidden');
            document.getElementById('stop-scan-btn').classList.add('hidden');
        });
    }
}
</script>
@endpush
