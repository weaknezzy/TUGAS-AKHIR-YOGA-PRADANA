@extends('main.menu_Catering')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-bold text-center mb-8 text-gray-800 font-sans">Paket Catering</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Paket Catering 1 -->
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center hover:scale-105 transition-transform">
                <h3 class="text-xl font-semibold mb-2 text-gray-700" title="Paket 1">Paket 1</h3>
                <p class="text-lg font-bold text-green-600 mb-4">Rp 16.000 <span class="font-normal text-gray-500">/ Box</span></p>
                <ul class="mb-6 space-y-2 text-gray-600">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Nasi Putih</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Ayam Tepung</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Sayur Nangka</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Timun</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Sambal</li>
                </ul>
                <button onclick="sendWhatsAppMessage('Paket 1', 16000, 'Nasi Putih, Ayam Tepung, Sayur Nangka, Timun, Sambal')" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-full shadow transition-colors">
                    <i class="fab fa-whatsapp mr-2"></i>Pesan
                </button>
            </div>
            <!-- Paket Catering 2 -->
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center hover:scale-105 transition-transform">
                <h3 class="text-xl font-semibold mb-2 text-gray-700" title="Paket 2">Paket 2</h3>
                <p class="text-lg font-bold text-green-600 mb-4">Rp 17.000 <span class="font-normal text-gray-500">/ Box</span></p>
                <ul class="mb-6 space-y-2 text-gray-600">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Nasi Putih</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Ayam Bakar</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Sayur Daun Ubi</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Bakwan Goreng</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Sambal</li>
                </ul>
                <button onclick="sendWhatsAppMessage('Paket 2', 17000, 'Nasi Putih, Ayam Bakar, Sayur Daun Ubi, Bakwan Goreng, Sambal')" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-full shadow transition-colors">
                    <i class="fab fa-whatsapp mr-2"></i>Pesan
                </button>
            </div>
            <!-- Paket Catering 3 -->
            <div class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-center hover:scale-105 transition-transform">
                <h3 class="text-xl font-semibold mb-2 text-gray-700" title="Paket 3">Paket 3</h3>
                <p class="text-lg font-bold text-green-600 mb-4">Rp 28.000 <span class="font-normal text-gray-500">/ Box</span></p>
                <ul class="mb-6 space-y-2 text-gray-600">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Nasi Putih</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Ayam Kecap</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Ikan Bakar</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Telur Sambal</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Sayur Sop</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Bakwan Goreng</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Sambal</li>
                </ul>
                <button onclick="sendWhatsAppMessage('Paket 3', 28000, 'Nasi Putih, Ayam Kecap, Ikan Bakar, Telur Sambal, Sayur Sop, Bakwan Goreng, Sambal')" class="inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-6 rounded-full shadow transition-colors">
                    <i class="fab fa-whatsapp mr-2"></i>Pesan
                </button>
            </div>
        </div>
        <!-- Informasi tambahan di bawah card -->
        <div class="mt-8 bg-white border-l-4" style="border-color: #f57c00;" class="p-4 rounded-lg shadow text-gray-800 max-w-2xl mx-auto">
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>Jam Pemesanan 07:00 - 21:00 WIB</strong></li>
                <li><strong>Minimal Pemesanan 20 Box</strong></li>
                <li><strong>Area Pengantaran Pontianak dan sekitarnya (tidak melayani luar Pontianak)</strong></li>
                <li><strong>Hanya berlaku untuk pemesanan via aplikasi Whatsapp dengan klik tombol Pesan</strong></li>
            </ul>
        </div>
    </div>

@push('scripts')
<script>
// Definisikan fungsi di global scope
window.sendWhatsAppMessage = function(packageName, price, menu) {
    console.log('Fungsi sendWhatsAppMessage dipanggil:', packageName, price, menu);
    
    const message = `PEMESANAN CATERING

Paket: ${packageName}
Harga per Box: Rp ${price.toLocaleString('id-ID')}
Menu yang Dipesan:
${menu}

Informasi Penting:
- Minimal Pemesanan: 20 Box
- Jam Operasional: 07:00 - 21:00 WIB
- Area Pengantaran: Pontianak dan sekitarnya

Mohon kirimkan detail pemesanan Anda:
- Nama Pemesan:
- Nomor Telepon:
- Jumlah Box:
- Alamat Pengantaran:
- Tanggal Pengantaran:
- Catatan Tambahan (opsional):

Terima kasih!`;
    
    // Encode message for WhatsApp URL
    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `https://wa.me/62895613483990?text=${encodedMessage}`;
    
    console.log('Membuka WhatsApp URL:', whatsappUrl);
    
    // Open WhatsApp
    window.open(whatsappUrl, '_blank');
};

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, fungsi sendWhatsAppMessage tersedia:', typeof window.sendWhatsAppMessage);
    
    // Fitur: Deteksi judul yang terpotong dan atur tooltip
    const menuTitles = document.querySelectorAll('.menu-card h3');
    menuTitles.forEach(title => {
        if (title.scrollHeight > title.clientHeight || title.scrollWidth > title.clientWidth) {
            title.style.cursor = 'help';
        } else {
            title.style.cursor = 'default';
            title.removeAttribute('title');
        }
    });
});
</script>
@endpush
@endsection
