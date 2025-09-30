<div class="space-y-4">
    <div class="text-center">
        <h3 class="text-lg font-medium text-gray-900 mb-2">
            Bukti Pembayaran - {{ $record->nama_pemesan }}
        </h3>
        <p class="text-sm text-gray-600 mb-4">
            {{ $record->acara }} - {{ $record->created_at->format('d/m/Y H:i') }}
        </p>
    </div>
    
    <div class="flex justify-center">
        <img 
            src="{{ Storage::url($record->bukti_pembayaran) }}" 
            alt="Bukti Pembayaran"
            class="max-w-full h-auto rounded-lg shadow-lg"
            style="max-height: 500px;"
        />
    </div>
    
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-medium text-gray-900 mb-2">Detail Pembayaran:</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium">Total Bayar:</span>
                <p class="text-green-600 font-semibold">Rp. {{ number_format($record->total_bayar, 0, ',', '.') }}</p>
            </div>
            <div>
                <span class="font-medium">Metode Pembayaran:</span>
                <p>{{ $record->metode_pembayaran }}</p>
            </div>
            <div>
                <span class="font-medium">Menu:</span>
                <p>{{ $record->menu }}</p>
            </div>
            <div>
                <span class="font-medium">Jumlah Porsi:</span>
                <p>{{ $record->jumlah_porsi }} porsi</p>
            </div>
        </div>
    </div>
</div>
