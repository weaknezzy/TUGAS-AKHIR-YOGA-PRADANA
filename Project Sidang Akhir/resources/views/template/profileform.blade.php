@extends('main.profile') <!-- Memanggil layout utama -->
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
    <div class="min-h-screen bg-gray-50 py-10">
        <div class="max-w-5xl mx-auto px-4">
                        <!-- Guest Search Form -->
            @if($isGuest && (empty($user['no_telp']) || $searchError))
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mb-8">
                    <div class="text-center mb-6">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Cek Status Pemesanan</h2>
                        <p class="text-gray-600">Masukkan nomor HP yang Anda gunakan saat pemesanan</p>
                        <p class="text-sm text-gray-500 mt-2">Format: 08xx-xxxx-xxxx atau +628xx-xxxx-xxxx</p>
                    </div>
                    
                    <div class="max-w-md mx-auto">
                        <div class="flex gap-4">
                            <input type="text" id="guestPhone" placeholder="Contoh: 08123456789" 
                                   value="{{ session('guest_phone') }}"
                                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <button onclick="searchOrders()" 
                                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-search mr-2"></i>Cari
                            </button>
                            @if(session('guest_phone') && !$searchError)
                                <button onclick="clearPhoneNumber()" 
                                        class="px-4 py-2 bg-gray-500 text-white font-semibold rounded-lg hover:bg-gray-600 transition-colors">
                                    <i class="fas fa-times mr-2"></i>Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Message for Guest Search -->
            @if($isGuest && $searchPerformed && $searchError)
                <div class="bg-red-50 border border-red-200 rounded-2xl shadow-xl p-6 mb-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-red-800">Pesanan Tidak Ditemukan</h3>
                            <p class="text-red-700 mt-1">{{ $searchError }}</p>
                            <div class="mt-3">
                                <button onclick="clearSearch()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <i class="fas fa-times mr-2"></i>Coba Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Success Message for Guest Search -->
            @if($isGuest && $searchPerformed && !$searchError && $orders->count() > 0 && !$isFromOrder && $hasNoTelp)
                <div class="bg-green-50 border border-green-200 rounded-2xl shadow-xl p-6 mb-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-2xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-green-800">Pesanan Ditemukan!</h3>
                            <p class="text-green-700 mt-1">Ditemukan {{ $orders->count() }} pesanan untuk nomor HP: {{ $user['no_telp'] }}</p>
                            <div class="mt-3">
                                <button onclick="searchNewNumber()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-search mr-2"></i>Cari Nomor Lain
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Message for Guest with Saved Phone -->
            @if($isGuest && !empty($user['no_telp']) && !$searchPerformed && !$isFromOrder && !session('order_success') && $hasNoTelp)
                <div class="bg-blue-50 border border-blue-200 rounded-2xl shadow-xl p-6 mb-8">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400 text-2xl"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-800">Riwayat Pencarian</h3>
                            <p class="text-blue-700 mt-1">Menampilkan pesanan untuk nomor HP: {{ $user['no_telp'] }}</p>
                            <div class="mt-3">
                                <button onclick="searchNewNumber()" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-search mr-2"></i>Cari Nomor Lain
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Profil Pengguna -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
                    <div class="flex flex-col items-center mb-6">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user['name'] ?? 'Guest') }}"
                            class="w-20 h-20 rounded-full shadow mb-4" alt="Avatar">
                        <h3 class="text-2xl font-bold text-blue-600">{{ $user['name'] ?? 'Guest' }}</h3>
                        <span class="text-sm text-gray-500">{{ ucfirst($user['role'] ?? 'Guest') }}</span>
                    </div>

                    <div class="space-y-4">
                        @if(!$isGuest)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Email:</label>
                            <div class="px-4 py-2 bg-gray-50 rounded-lg text-gray-700">
                                {{ $user['email'] ?? 'Tidak tersedia' }}
                            </div>
                        </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Nomor Telepon:</label>
                            <div class="px-4 py-2 bg-gray-50 rounded-lg text-gray-700">
                                {{ $user['no_telp'] ?? 'Tidak tersedia' }}
                            </div>
                        </div>

                        @if(!$isGuest)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Alamat:</label>
                            <div class="px-4 py-2 bg-gray-50 rounded-lg text-gray-700">
                                {{ $user['alamat'] ?? 'Tidak tersedia' }}
                            </div>
                        </div>

                        <div class="pt-4">
                            <button onclick="editProfile()" class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                                <i class="fas fa-edit"></i>
                                Edit Profil
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Status Pemesanan -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 flex flex-col">
                    <h3 class="text-2xl font-bold mb-6 text-blue-600">Status Pemesanan</h3>
                    
                    @if($orders->count() > 0)
                        <div class="space-y-6">
                            @foreach($orders as $order)
                                <div class="border-b pb-4 last:border-b-0 last:pb-0">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h4 class="font-semibold text-gray-800">{{ $order['order_id'] }}</h4>
                                            <p class="text-sm text-gray-500">{{ $order['date'] }}</p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                            @if($order['status'] === 'Pending') bg-yellow-100 text-yellow-800
                                            @elseif($order['status'] === 'Diproses') bg-blue-100 text-blue-800
                                            @elseif($order['status'] === 'Selesai') bg-green-100 text-green-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ $order['status'] }}
                                        </span>
                                    </div>

                                    <div class="flex gap-2">
                                        <button onclick="showOrderDetail('{{ $order['order_id'] }}')"
                                            class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 transition-colors duration-200 text-white font-semibold py-2 px-4 rounded-lg shadow">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </button>
                                        @if($order['status'] === 'Diproses')
                                            <button onclick="confirmDelivery('{{ $order['order_id'] }}')"
                                                class="inline-flex items-center gap-2 bg-green-500 hover:bg-green-600 transition-colors duration-200 text-white font-semibold py-2 px-4 rounded-lg shadow">
                                                <i class="fas fa-check"></i> Terima Pesanan
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-shopping-bag text-6xl"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak Ada Pemesanan</h3>
                            <p class="text-gray-500">
                                @if($isGuest && empty($user['no_telp']))
                                    Silakan masukkan nomor HP Anda untuk melihat status pemesanan.
                                @elseif($isGuest)
                                    Belum ada pemesanan untuk nomor HP ini.
                                @else
                                    Anda belum memiliki pemesanan.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('template.orderDetailModal')

    <!-- Modal Edit Profile -->
    <div id="editProfileModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
        <div class="bg-white w-full max-w-md rounded-lg shadow-lg p-8 relative">
            <button onclick="closeEditProfile()" class="absolute top-2 right-3 text-2xl">&times;</button>
            <h2 class="text-2xl font-bold mb-6 text-blue-600">Edit Profil</h2>
            <form id="editProfileForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                    <input type="text" name="name" id="editName" class="w-full border rounded p-2" required value="{{ $user['name'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="editEmail" class="w-full border rounded p-2" required value="{{ $user['email'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nomor Telepon</label>
                    <input type="text" name="no_telp" id="editNoTelp" class="w-full border rounded p-2" required value="{{ $user['no_telp'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Alamat</label>
                    <textarea name="alamat" id="editAlamat" class="w-full border rounded p-2" required>{{ $user['alamat'] ?? '' }}</textarea>
                </div>
                <div id="editProfileError" class="text-red-600 text-sm"></div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        function searchOrders() {
            const no_telp = document.getElementById('guestPhone').value.trim();
            if (!no_telp) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Nomor HP Kosong',
                    text: 'Silakan masukkan nomor HP Anda untuk mencari riwayat pesanan.',
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

            // Validasi format nomor HP - mendukung nomor HP Indonesia yang lebih panjang
            const phoneRegex = /^(\+62|62|0)8[1-9][0-9]{6,11}$/;
            if (!phoneRegex.test(no_telp)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format Nomor HP Salah',
                    text: 'Silakan masukkan nomor HP yang valid (contoh: 08123456789, 0895613483990)',
                    confirmButtonColor: '#d33',
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

            // Tampilkan loading
            Swal.fire({
                title: 'Mencari Pesanan...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Redirect ke halaman dengan parameter
            window.location.href = `{{ route('user.profile') }}?no_telp=${encodeURIComponent(no_telp)}`;
        }

        function clearSearch() {
            // Hapus parameter dari URL dan reload halaman
            const url = new URL(window.location);
            url.searchParams.delete('no_telp');
            window.location.href = url.toString();
        }

        function searchNewNumber() {
            // Hapus nomor HP dari session dan kembali ke tampilan pencarian
            fetch('{{ route("user.profile") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'clear_phone'
                })
            }).then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    window.location.href = '{{ route("user.profile") }}';
                }
            });
        }

        function clearPhoneNumber() {
            Swal.fire({
                title: 'Hapus Nomor HP',
                text: 'Apakah Anda yakin ingin menghapus nomor HP yang tersimpan?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hapus nomor HP dari session dan reload halaman
                    fetch('{{ route("user.profile") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'clear_phone'
                        })
                    }).then(response => {
                        if (response.redirected) {
                            window.location.href = response.url;
                        } else {
                            window.location.href = '{{ route("user.profile") }}';
                        }
                    });
                }
            });
        }

        document.getElementById('guestPhone')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchOrders();
            }
        });

        function confirmDelivery(orderId) {
            Swal.fire({
                title: 'Konfirmasi Penerimaan',
                text: 'Apakah Anda yakin telah menerima pesanan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4CAF50',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Saya Sudah Menerima',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    processConfirmDelivery(orderId);
                }
            });
        }

        function processConfirmDelivery(orderId) {

            const formData = new FormData();
            @if($isGuest)
            formData.append('no_telp', '{{ $user["no_telp"] ?? "" }}');
            @endif
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch(`/pesanan/${orderId}/confirm-delivery`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Dikonfirmasi!',
                        text: 'Terima kasih! Pesanan telah dikonfirmasi selesai.',
                        confirmButtonColor: '#4CAF50',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengkonfirmasi',
                        text: data.error || 'Terjadi kesalahan saat mengkonfirmasi pesanan.',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: 'Terjadi kesalahan saat mengkonfirmasi pesanan.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            });
        }

        function showOrderDetail(orderId) {
            const modal = document.getElementById('orderDetailModal');
            const loading = document.getElementById('loadingDetail');
            const error = document.getElementById('errorDetail');
            const details = document.getElementById('orderDetails');

            // Show modal and loading
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            error.classList.add('hidden');
            details.classList.add('hidden');

            // Add no_telp parameter for guest users
            let url = `/order-detail/${orderId}`;
            @if($isGuest)
            url += `?no_telp={{ $user["no_telp"] ?? "" }}`;
            @endif

            // Fetch order details
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    // Hide loading
                    loading.classList.add('hidden');
                    
                    // Update order details
                    document.getElementById('orderNumber').textContent = `#${data.order.id}`;
                    document.getElementById('orderDate').textContent = new Date(data.order.created_at).toLocaleString('id-ID');
                    document.getElementById('orderStatus').textContent = data.order.status;
                    document.getElementById('paymentMethod').textContent = data.order.payment_method;
                    
                    document.getElementById('customerName').textContent = data.order.customer_name;
                    document.getElementById('customerPhone').textContent = data.order.no_telp;
                    
                    document.getElementById('shippingAddress').textContent = data.shipping_address;
                    
                    // Update items
                    const itemsContainer = document.getElementById('orderItems');
                    itemsContainer.innerHTML = '';
                    data.order_items.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'flex justify-between items-center py-2';
                        itemDiv.innerHTML = `
                            <span class="flex-1">${item.name} x ${item.quantity}</span>
                            <span class="font-medium">Rp ${parseInt(item.price * item.quantity).toLocaleString('id-ID')}</span>
                        `;
                        itemsContainer.appendChild(itemDiv);
                    });
                    
                    // Update amounts
                    document.getElementById('subtotalAmount').textContent = `Rp ${parseInt(data.subtotal).toLocaleString('id-ID')}`;
                    document.getElementById('shippingCost').textContent = `Rp ${parseInt(data.shipping_cost).toLocaleString('id-ID')}`;
                    document.getElementById('totalAmount').textContent = `Rp ${parseInt(data.total_with_shipping).toLocaleString('id-ID')}`;
                    
                    // Show notes if exists
                    const notesSection = document.getElementById('orderNotes');
                    const notesContent = document.getElementById('notes');
                    if (data.order.note) {
                        notesContent.textContent = data.order.note;
                        notesSection.classList.remove('hidden');
                    } else {
                        notesSection.classList.add('hidden');
                    }
                    
                    // Show details
                    details.classList.remove('hidden');
                })
                .catch(err => {
                    loading.classList.add('hidden');
                    error.classList.remove('hidden');
                    console.error('Error fetching order details:', err);
                });
        }

        function editProfile() {
            document.getElementById('editProfileModal').classList.remove('hidden');
        }
        function closeEditProfile() {
            document.getElementById('editProfileModal').classList.add('hidden');
        }
        document.getElementById('editProfileForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const data = {
                name: form.name.value,
                email: form.email.value,
                no_telp: form.no_telp.value,
                alamat: form.alamat.value,
                _token: document.querySelector('meta[name="csrf-token"]').content
            };
            try {
                const response = await fetch("{{ route('user.profile.update') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': data._token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    document.getElementById('editProfileError').textContent = result.message || 'Gagal memperbarui profil';
                }
            } catch (err) {
                document.getElementById('editProfileError').textContent = 'Terjadi kesalahan saat memperbarui profil';
            }
        });

        // Initialize event listeners when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Add click event listener to close button
            const closeBtn = document.querySelector('#orderDetailModal button[onclick="closeOrderDetail()"]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeOrderDetail();
                });
            }

            // Tampilkan pemberitahuan jika ada hasil pencarian
            @if($isGuest && $searchPerformed && !$isFromOrder && $hasNoTelp)
                @if($searchError)
                    // Tampilkan pemberitahuan error
                    Swal.fire({
                        icon: 'error',
                        title: 'Pesanan Tidak Ditemukan',
                        text: '{{ $searchError }}',
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                @elseif($orders->count() > 0)
                    // Tampilkan pemberitahuan sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Pesanan Ditemukan!',
                        text: 'Ditemukan {{ $orders->count() }} pesanan untuk nomor HP: {{ $user["no_telp"] }}',
                        confirmButtonColor: '#4CAF50',
                        confirmButtonText: 'OK',
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                @endif
            @endif
        });
    </script>
@endsection
