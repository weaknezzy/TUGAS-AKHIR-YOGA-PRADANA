<!-- Modal Detail Pemesanan -->
<div id="orderDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white w-11/12 md:w-2/3 lg:w-1/2 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <!-- Loading State -->
        <div id="loadingDetail" class="p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-4 text-gray-600">Memuat detail pesanan...</p>
        </div>

        <!-- Error State -->
        <div id="errorDetail" class="hidden p-8 text-center">
            <div class="text-red-500 text-6xl mb-4">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">Gagal Memuat Data</h3>
            <p class="text-gray-600">Maaf, terjadi kesalahan saat memuat detail pesanan.</p>
        </div>

        <!-- Success State -->
        <div id="orderDetails" class="hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">Detail Pesanan</h3>
                        <p class="text-gray-600">Order <span id="orderNumber"></span></p>
                    </div>
                    <button onclick="closeOrderDetail()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Informasi Pesanan -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3">Informasi Pesanan</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Tanggal Pemesanan</p>
                            <p class="font-medium text-gray-800" id="orderDate"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Metode Pembayaran</p>
                            <p class="font-medium text-gray-800" id="paymentMethod"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <span id="orderStatus" class="inline-block px-2 py-1 rounded text-xs font-semibold"></span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pelanggan -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3">Informasi Pelanggan</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Pemesan</p>
                            <p class="font-medium text-gray-800" id="customerName"></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Nomor Telepon</p>
                            <p class="font-medium text-gray-800" id="customerPhone"></p>
                        </div>
                    </div>
                </div>

                <!-- Alamat Pengiriman -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3">Alamat Pengiriman</h4>
                    <p class="text-gray-800" id="shippingAddress"></p>
                </div>

                <!-- Item Pesanan -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3">Item Pesanan</h4>
                    <div id="orderItems" class="space-y-2"></div>
                </div>

                <!-- Catatan -->
                <div id="orderNotes" class="hidden">
                    <h4 class="font-semibold text-gray-800 mb-3">Catatan</h4>
                    <p class="text-gray-800" id="notes"></p>
                </div>

                <!-- Rincian Biaya -->
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3">Rincian Biaya</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-800" id="subtotalAmount"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Biaya Pengiriman</span>
                            <span class="font-medium text-gray-800" id="shippingCost"></span>
                        </div>
                        <div class="flex justify-between pt-2 border-t">
                            <span class="font-semibold text-gray-800">Total</span>
                            <span class="font-semibold text-gray-800" id="totalAmount"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function closeOrderDetail() {
        const modal = document.getElementById('orderDetailModal');
        modal.classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('orderDetailModal').addEventListener('click', function(event) {
        // Check if the click is on the modal background (not the modal content)
        if (event.target === this) {
            closeOrderDetail();
        }
    });
</script> 