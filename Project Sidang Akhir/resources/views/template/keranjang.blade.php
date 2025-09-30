<link href="{{ asset('css/cart.css') }}" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>

<div id="cartOverlay" class="cart-overlay hidden"></div>

<div class="cart-popup" id="cartPopup">
    {{-- Header --}}
    <div class="cart-header">
        <h3>🛒 Keranjang</h3>
        <button class="close-btn" onclick="toggleCart()">&times;</button>
    </div>

    {{-- Konten keranjang --}}
    <div class="cart-content max-h-64 overflow-y-auto">
        @if ($keranjangItems->count() > 0)
            @foreach ($keranjangItems as $item)
                <div class="cart-item">
                    <div class="item-details">
                        <h4>{{ $item->menu->nama_menu }}</h4>
                        <p>Harga: Rp {{ number_format($item->menu->harga, 0, ',', '.') }}</p>

                        <form action="{{ route('keranjang.update', $item->id) }}" method="POST"
                            class="update-form flex items-center mt-2">
                            @csrf @method('PATCH')

                            <button type="button" class="decrement px-2 bg-gray-200 rounded">-</button>
                            <input type="number" name="jumlah" value="{{ $item->jumlah }}" min="1"
                                class="w-12 text-center border mx-1 rounded">
                            <button type="button" class="increment px-2 bg-gray-200 rounded">+</button>

                            <button type="submit" class="ml-2 text-sm text-blue-500">✔</button>
                        </form>
                    </div>

                    <form action="{{ route('keranjang.remove', $item->id) }}" method="POST" class="remove-form">
                        @csrf @method('DELETE')
                        <button type="submit" class="remove-btn" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            @endforeach
        @else
            <div class="empty-cart-container text-center py-8">
                <div class="empty-cart-icon mb-4">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300"></i>
                </div>
                <p class="empty-cart text-gray-500 mb-2">Keranjang Belanja Kosong</p>
                <p class="empty-cart-subtitle text-sm text-gray-400">Belum ada menu yang ditambahkan</p>
            </div>
        @endif
    </div>

    {{-- Footer --}}
    <div class="cart-footer">
        <div class="total">
            Total: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
        </div>

        @if ($keranjangItems->count() > 0)
            {{-- Panggil modal checkout yg terpisah --}}
            <button onclick="prepareCheckout()"
                class="checkout-btn bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded w-full mt-2">
                Checkout
            </button>
        @else
            {{-- Tombol checkout disabled ketika keranjang kosong --}}
            <button disabled
                class="checkout-btn-disabled bg-gray-400 text-gray-600 px-4 py-2 rounded w-full mt-2 cursor-not-allowed"
                onclick="showEmptyCartAlert()">
                Checkout
            </button>
        @endif
    </div>
</div>

<script>
    function toggleCart() {
        document.getElementById('cartPopup').classList.toggle('show');
        document.getElementById('cartOverlay').classList.toggle('hidden');
    }

    function prepareCheckout() {
        // Cek apakah ada item di keranjang
        const cartItems = document.querySelectorAll('.cart-item');
        if (cartItems.length === 0) {
            showEmptyCartAlert();
            return;
        }

        const items = [];
        let total = 0;

        document.querySelectorAll('.cart-item').forEach(item => {
            const name = item.querySelector('h4').textContent.trim();
            const hargaText = item.querySelector('p').textContent.replace(/[^\d]/g, '');
            const harga = parseInt(hargaText);
            const jumlah = parseInt(item.querySelector('input[name="jumlah"]').value);

            items.push({
                nama_menu: name,
                harga: harga,
                jumlah: jumlah
            });
            total += harga * jumlah;
        });

        setCheckoutData(items, total);
    }

    function showEmptyCartAlert() {
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
    }


    document.addEventListener('DOMContentLoaded', () => {
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Sukses',
                text: '{{ session('success') }}',
                timer: 2000,
                showConfirmButton: false
            });
        @endif

        document.querySelectorAll('.remove-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Yakin hapus item ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Update tombol checkout sebelum submit
                        const cartItems = document.querySelectorAll('.cart-item');
                        if (cartItems.length <= 1) {
                            updateCheckoutButton();
                        }
                        form.submit();
                    }
                });
            });
        });

        document.querySelectorAll('.update-form').forEach(form => {
            const decrement = form.querySelector('.decrement');
            const increment = form.querySelector('.increment');
            const input = form.querySelector('input[name="jumlah"]');

            decrement.addEventListener('click', () => {
                let val = parseInt(input.value);
                if (val > 1) {
                    input.value = val - 1;
                    // Update tombol checkout jika jumlah menjadi 0
                    if (val - 1 === 0) {
                        updateCheckoutButton();
                    }
                }
            });

            increment.addEventListener('click', () => {
                let val = parseInt(input.value);
                input.value = val + 1;
            });

            // Event listener untuk form submit
            form.addEventListener('submit', function(e) {
                // Update tombol checkout sebelum submit
                setTimeout(() => {
                    updateCheckoutButton();
                }, 100);
            });
        });

        // Update status tombol checkout setelah operasi keranjang
        function updateCheckoutButton() {
            const cartItems = document.querySelectorAll('.cart-item');
            const checkoutBtn = document.querySelector('.checkout-btn');
            const checkoutBtnDisabled = document.querySelector('.checkout-btn-disabled');
            
            // Cek apakah ada item dengan jumlah > 0
            let hasValidItems = false;
            cartItems.forEach(item => {
                const input = item.querySelector('input[name="jumlah"]');
                if (input && parseInt(input.value) > 0) {
                    hasValidItems = true;
                }
            });
            
            if (cartItems.length === 0 || !hasValidItems) {
                if (checkoutBtn) checkoutBtn.style.display = 'none';
                if (checkoutBtnDisabled) checkoutBtnDisabled.style.display = 'block';
            } else {
                if (checkoutBtn) checkoutBtn.style.display = 'block';
                if (checkoutBtnDisabled) checkoutBtnDisabled.style.display = 'none';
            }
        }

        // Panggil updateCheckoutButton setelah operasi keranjang
        updateCheckoutButton();
    });
</script>
