<script src="https://cdn.tailwindcss.com"></script>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<!-- Leaflet CSS & JS (Gratis, tidak perlu API key) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
    <div class="bg-white w-11/12 md:w-1/2 rounded shadow-lg p-6 max-h-[90vh] overflow-y-auto relative">
        <button onclick="toggleCheckout()" class="absolute top-2 right-3 text-xl">&times;</button>

        <h2 class="text-xl font-bold mb-4">Checkout</h2>

        <form id="checkoutForm" action="{{ route('pemesanan.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Nama & Phone untuk semua user --}}
            <div>
                <label class="block font-medium">Nama Pemesan</label>
                <input type="text" name="customer_name" class="w-full border rounded p-2" 
                    value="{{ Auth::check() ? Auth::user()->name : old('customer_name') }}" 
                    required>
            </div>

            <div>
                <label class="block font-medium">Nomor HP</label>
                <input type="text" name="no_telp" class="w-full border rounded p-2" 
                    value="{{ Auth::check() ? Auth::user()->no_telp : old('no_telp') }}" 
                    required>
            </div>

            <div>
                <label class="block font-medium">Alamat Pengiriman</label>
                <textarea name="alamat" id="alamatInput" rows="2" class="w-full border rounded p-2" required placeholder="Ketik alamat atau pilih lokasi di peta">{{ Auth::check() ? Auth::user()->alamat : old('alamat') }}</textarea>
                <p class="text-sm text-gray-600 mt-1">
                    Gratis ongkir untuk wilayah: Jeruju, Jalan Karet, dan Jalan TPI
                </p>
                
                <!-- Maps Container -->
                <div class="mt-3">
                    <label class="block font-medium mb-2">Pilih Lokasi di Peta</label>
                    <div id="map" class="w-full h-64 border rounded-lg"></div>
                    <div class="mt-2 text-sm text-gray-600">
                        <p>📍 Klik pada peta untuk memilih lokasi yang tepat</p>
                        <p>🔍 Atau gunakan search box di atas peta</p>
                    </div>
                </div>
                
                <!-- Koordinat Display -->
                <div class="mt-2 p-2 bg-gray-100 rounded text-sm">
                    <div class="flex justify-between">
                        <span>Latitude:</span>
                        <span id="latitudeDisplay">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Longitude:</span>
                        <span id="longitudeDisplay">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Jarak dari Toko:</span>
                        <span id="roadDistanceDisplay">-</span>
                    </div>
                    {{-- <div class="flex justify-between text-xs text-gray-600">
                        <span>Tarif:</span>
                        <span>Rp 3.000/km</span>
                    </div> --}}
                </div>
                
                <!-- Hidden inputs untuk koordinat -->
                <input type="hidden" name="latitude" id="latitudeInput">
                <input type="hidden" name="longitude" id="longitudeInput">
            </div>
        
            {{-- Order Items (biasanya isi JSON/string hidden) --}}
            <input type="hidden" name="order_items" id="orderItemsInput">
            <input type="hidden" name="order_id" id="orderIdInput"><!-- Tambahkan hidden input order_id -->

            <div>
                <label class="block font-medium">Catatan (opsional)</label>
                <textarea name="note" rows="2" class="w-full border rounded p-2">{{ old('note') }}</textarea>
            </div>

            {{-- Rincian Biaya --}}
            <div class="border rounded p-4 space-y-2">
                <h3 class="font-medium">Rincian Biaya:</h3>
                
                <div class="flex justify-between">
                    <span>Subtotal Pesanan:</span>
                    <span>Rp. <span id="subtotalDisplay">0</span></span>
                </div>

                <div class="flex justify-between">
                    <span>Ongkos Kirim:</span>
                    <span>Rp. <span id="shippingDisplay">0</span></span>
                </div>

                <div class="border-t pt-2 font-medium flex justify-between">
                    <span>Total Pembayaran:</span>
                    <span>Rp. <span id="totalDisplay">0</span></span>
                </div>
            </div>

            {{-- Hidden inputs untuk form submission --}}
            <input type="hidden" name="total_amount" id="totalAmountInput">
            <input type="hidden" name="ongkir" id="shippingCostInput">
            <input type="hidden" id="subtotalInput">

            <div>
                <label class="block font-medium">Metode Pembayaran</label>
                <select name="payment_method" class="w-full border rounded p-2" required>
                    <option value="">-- Pilih --</option>
                    <option value="COD">COD</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>

            <button type="submit" id="submitCheckout" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded">
                Kirim Pesanan
            </button>
        </form>
    </div>
</div>

<script>
    function toggleCheckout() {
        document.getElementById('checkoutModal').classList.toggle('hidden');
    }

    // Format number to currency
    function formatCurrency(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Update display values
    function updateDisplayValues(subtotal, shipping) {
        document.getElementById('subtotalDisplay').textContent = formatCurrency(subtotal);
        document.getElementById('shippingDisplay').textContent = formatCurrency(shipping);
        document.getElementById('totalDisplay').textContent = formatCurrency(subtotal + shipping);
        
        // Update hidden inputs
        document.getElementById('subtotalInput').value = subtotal;
        document.getElementById('shippingCostInput').value = shipping;
        document.getElementById('totalAmountInput').value = subtotal + shipping;
    }

    // Fungsi untuk menghitung ongkir
    async function calculateShipping() {
        const alamat = document.getElementById('alamatInput').value;
        if (!alamat) return;

        try {
            const response = await fetch('{{ route("calculate.shipping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ alamat })
            });

            const data = await response.json();
            const shippingCost = parseFloat(data.shipping_cost) || 0;
            const subtotal = parseFloat(document.getElementById('subtotalInput').value) || 0;
            
            // Update display dan hidden inputs
            updateDisplayValues(subtotal, shippingCost);
        } catch (error) {
            console.error('Error calculating shipping:', error);
        }
    }

    // Event listener untuk perubahan alamat
    document.getElementById('alamatInput').addEventListener('blur', calculateShipping);

    // Fungsi untuk menampilkan checkout modal dan mengisi data
    function setCheckoutData(items, total) {
        // Validasi items tidak kosong
        if (!items || items.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Belum ada menu yang ditambahkan ke keranjang. Silakan pilih menu terlebih dahulu.',
                confirmButtonColor: '#ff9800',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Validasi total lebih dari 0
        if (!total || total <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Total Belanja Tidak Valid',
                text: 'Total belanja tidak valid. Silakan pilih menu terlebih dahulu.',
                confirmButtonColor: '#ff9800',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        document.getElementById('orderItemsInput').value = JSON.stringify(items);
        // Generate order_id unik di frontend
        const orderId = 'ORDER-' + Math.random().toString(36).substr(2, 12);
        document.getElementById('orderIdInput').value = orderId;
        // Set subtotal dan update display
        const subtotal = parseFloat(total) || 0;
        document.getElementById('subtotalInput').value = subtotal;
        updateDisplayValues(subtotal, 0);
        // Calculate shipping if address is already filled
        const alamat = document.getElementById('alamatInput').value;
        if (alamat) {
            calculateShipping();
        }
        toggleCheckout();
    }

    // Integrasi Snap Midtrans
    const checkoutForm = document.getElementById('checkoutForm');
    const paymentMethodSelect = checkoutForm.querySelector('select[name="payment_method"]');
    const submitBtn = document.getElementById('submitCheckout');

    // Gantikan event submit form
    checkoutForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        
        // Validasi keranjang tidak kosong di frontend
        const orderItemsInput = document.getElementById('orderItemsInput');
        const orderItems = JSON.parse(orderItemsInput.value || '[]');
        
        if (!orderItems || orderItems.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Keranjang Kosong',
                text: 'Belum ada menu yang ditambahkan ke keranjang. Silakan pilih menu terlebih dahulu.',
                confirmButtonColor: '#ff9800',
                confirmButtonText: 'OK',
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
            return;
        }
        
        const formData = new FormData(form);
        const submitBtn = document.getElementById('submitCheckout');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses pesanan...';

        // Ambil metode pembayaran
        const paymentMethod = form.payment_method.value;

        // Submit pesanan ke backend
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(errorData => {
                    throw new Error(errorData.error || 'Terjadi kesalahan pada server');
                });
            }
            return res.json();
        })
        .then(data => {
            if (data.success && data.order_id) {
                // Jika COD, langsung reload/tampilkan pesan sukses tanpa Snap
                if (paymentMethod === 'COD') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Berhasil!',
                        text: 'Pesanan COD berhasil dikirim!',
                        confirmButtonColor: '#4CAF50',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }
                // Selain COD, request Snap Token
                fetch("{{ route('midtrans.snap-token') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        order_id: data.order_id,
                        customer_name: form.customer_name.value,
                        no_telp: form.no_telp.value,
                        total_amount: form.total_amount.value
                    })
                })
                .then(res => res.json())
                .then(snap => {
                    if (snap.snap_token) {
                        window.snap.pay(snap.snap_token, {
                            onSuccess: function(result) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Pembayaran Berhasil!',
                                    text: 'Pembayaran berhasil! Pesanan Anda telah diterima.',
                                    confirmButtonColor: '#4CAF50',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            onPending: function(result) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Pesanan Berhasil Dibuat!',
                                    text: 'Pesanan Anda berhasil dibuat! Silakan selesaikan pembayaran.',
                                    confirmButtonColor: '#2196F3',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            },
                            onError: function(result) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Pembayaran Gagal',
                                    text: 'Pembayaran gagal. Silakan coba lagi.',
                                    confirmButtonColor: '#d33',
                                    confirmButtonText: 'OK'
                                });
                            },
                            onClose: function() {
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Kirim Pesanan';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mendapatkan Snap Token',
                            text: 'Gagal mendapatkan Snap Token. Coba lagi.',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Kirim Pesanan';
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: data.error || 'Terjadi kesalahan. Coba lagi.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
                submitBtn.disabled = false;
                submitBtn.textContent = 'Kirim Pesanan';
            }
        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: err.message || 'Terjadi kesalahan. Coba lagi.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
            submitBtn.disabled = false;
            submitBtn.textContent = 'Kirim Pesanan';
        });
    });

    // Leaflet Maps Integration (Gratis, tidak perlu API key)
    let map, marker, storeMarker;
    let storeLat = {{ config('shipping.store_coordinates.lat') }};
    let storeLng = {{ config('shipping.store_coordinates.lng') }};
    let selectedLat, selectedLng;

    // Initialize map when modal opens
    function initializeMap() {
        if (map) return; // Already initialized
        
        // Center map on Pontianak
        const pontianak = [{{ config('shipping.store_coordinates.lat') }}, {{ config('shipping.store_coordinates.lng') }}];
        
        map = L.map('map').setView(pontianak, 12);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add store marker (red)
        storeMarker = L.marker(pontianak, {
            title: 'Lokasi Toko',
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background-color: #ff0000; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(map);

        // Add store popup
        storeMarker.bindPopup('<div style="padding: 5px;"><strong>Lokasi Toko</strong><br>Pusat Catering</div>');

        // Add search box
        const searchContainer = document.createElement('div');
        searchContainer.style.cssText = `
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 5px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        `;
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Cari alamat di Pontianak...';
        searchInput.style.cssText = `
            width: 250px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        `;
        
        const searchButton = document.createElement('button');
        searchButton.textContent = 'Cari';
        searchButton.style.cssText = `
            margin-left: 5px;
            padding: 8px 12px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        `;
        
        searchContainer.appendChild(searchInput);
        searchContainer.appendChild(searchButton);
        document.getElementById('map').appendChild(searchContainer);

        // Search functionality
        searchButton.addEventListener('click', () => {
            const query = searchInput.value;
            if (query) {
                searchAddress(query);
            }
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                const query = searchInput.value;
                if (query) {
                    searchAddress(query);
                }
            }
        });

        // Add click listener to map
        map.on('click', (event) => {
            const lat = event.latlng.lat;
            const lng = event.latlng.lng;
            
            // Reverse geocoding using Nominatim
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    const address = data.display_name || 'Lokasi yang dipilih';
                    addMarker([lat, lng], address);
                })
                .catch(error => {
                    console.log('Geocoding failed:', error);
                    addMarker([lat, lng], 'Lokasi yang dipilih');
                });
        });
    }

    // Search address function
    function searchAddress(query) {
        const searchQuery = query + ', Pontianak, Indonesia';
        
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchQuery)}&limit=1&countrycodes=id`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    
                    map.setView([lat, lng], 16);
                    addMarker([lat, lng], result.display_name);
                } else {
                    alert('Alamat tidak ditemukan. Silakan coba dengan alamat yang lebih spesifik.');
                }
            })
            .catch(error => {
                console.error('Search failed:', error);
                alert('Gagal mencari alamat. Silakan coba lagi.');
            });
    }

    // Add marker function
    function addMarker(location, address) {
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }

        // Add new marker (blue)
        marker = L.marker(location, {
            title: 'Lokasi Pengiriman',
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: '<div style="background-color: #007bff; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(map);

        // Update coordinates
        selectedLat = location[0];
        selectedLng = location[1];
        
        document.getElementById('latitudeDisplay').textContent = selectedLat.toFixed(6);
        document.getElementById('longitudeDisplay').textContent = selectedLng.toFixed(6);
        document.getElementById('latitudeInput').value = selectedLat;
        document.getElementById('longitudeInput').value = selectedLng;

        // Update address input
        document.getElementById('alamatInput').value = address;

        // Calculate distance
        calculateDistanceFromStore(selectedLat, selectedLng);

        // Add popup
        marker.bindPopup(`
            <div style="padding: 5px;">
                <strong>Lokasi Pengiriman</strong><br>
                ${address}<br>
                <small>Klik untuk menghapus</small>
            </div>
        `);

        // Remove marker on click
        marker.on('click', () => {
            map.removeLayer(marker);
            marker = null;
            selectedLat = null;
            selectedLng = null;
            document.getElementById('latitudeDisplay').textContent = '-';
            document.getElementById('longitudeDisplay').textContent = '-';
            document.getElementById('roadDistanceDisplay').textContent = '-';
            document.getElementById('latitudeInput').value = '';
            document.getElementById('longitudeInput').value = '';
        });

        // Calculate shipping cost
        calculateShippingWithCoordinates();
    }

    // Calculate distance from store using Haversine formula
    function calculateDistanceFromStore(lat, lng) {
        const R = 6371; // Radius bumi dalam kilometer
        const dLat = (lat - storeLat) * Math.PI / 180;
        const dLng = (lng - storeLng) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(storeLat * Math.PI / 180) * Math.cos(lat * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const straightDistance = R * c;
        
        // Faktor koreksi untuk jarak jalan (10% lebih jauh)
        const roadDistanceFactor = 1.1;
        const roadDistance = straightDistance * roadDistanceFactor;
        
        // Update display
        document.getElementById('roadDistanceDisplay').textContent = roadDistance.toFixed(2) + ' km';
        
        return roadDistance; // Return road distance for shipping calculation
    }

    // Calculate shipping with coordinates
    async function calculateShippingWithCoordinates() {
        if (!selectedLat || !selectedLng) return;

        try {
            const response = await fetch('{{ route("calculate.shipping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    alamat: document.getElementById('alamatInput').value,
                    latitude: selectedLat,
                    longitude: selectedLng
                })
            });

            const data = await response.json();
            const shippingCost = parseFloat(data.shipping_cost) || 0;
            const subtotal = parseFloat(document.getElementById('subtotalInput').value) || 0;
            
            updateDisplayValues(subtotal, shippingCost);
        } catch (error) {
            console.error('Error calculating shipping:', error);
        }
    }

    // Initialize map when checkout modal opens
    const originalToggleCheckout = toggleCheckout;
    toggleCheckout = function() {
        originalToggleCheckout();
        
        // Initialize map after modal is shown
        setTimeout(() => {
            if (!document.getElementById('checkoutModal').classList.contains('hidden')) {
                initializeMap();
            }
        }, 100);
    };

    // Update shipping calculation to use coordinates if available
    const originalCalculateShipping = calculateShipping;
    calculateShipping = async function() {
        if (selectedLat && selectedLng) {
            await calculateShippingWithCoordinates();
        } else {
            await originalCalculateShipping();
        }
    };
</script>
