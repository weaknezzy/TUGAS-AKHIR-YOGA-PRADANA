<!-- Link Font Google -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Link Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<link href="{{ asset('css/navbarMenu.css') }}" rel="stylesheet">

<body>
    <nav class="navbar">
        <!-- Logo -->
        <a href="#" class="navbar-logo">
            <img src="/images/logo.png" alt="Logo Pakde Along" class="navbar-logo-img">
            <div class="navbar-logo-text">
                Warung Makan <span>Rumah Pakde Along</span>
            </div>
        </a>

        <!-- Tombol Hamburger (Mobile) -->
        <div class="navbar-toggle" id="hamburger">
            <i class="fas fa-bars"></i>
        </div>

        <!-- Menu Navigasi -->
        <div class="navbar-nav" id="navbarMenu">
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('main.menuMakanan') }}">Makanan</a>
            <a href="{{ route('main.menuMinuman') }}">Minuman</a>
            <a href="{{ route('main.menuCatering') }}">Catering</a>
            <a href="{{ route('user.profile') }}">Status Pemesanan</a>
        </div>

        <!-- Bagian Profil dan Keranjang -->
        <div class="profile-section">
            <!-- Ikon Keranjang -->
            <div class="cart-icon">
                <a href="javascript:void(0);" onclick="toggleCart()">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">{{ $keranjangItems->count() ?? 0 }}</span>
                </a>
            </div>

            @include('template.keranjang') {{-- Menampilkan popup keranjang --}}
            @include('template.formpemesanan')

            <!-- Dropdown Profile -->
            <div class="profile-dropdown">
                <button class="profile-btn" onclick="toggleDropdown()">
                    <i class="fas fa-user"></i>
                    <span>{{ Auth::check() ? Auth::user()->name : 'Login' }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>

                <div class="dropdown-menu" id="profileDropdown">
                    @if (Auth::check())
                        <a href="{{ route('user.profile') }}" class="dropdown-item">
                            <i class="fas fa-user-circle"></i> Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="dropdown-item">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="{{ route('register') }}" class="dropdown-item">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- JavaScript untuk Dropdown & Cart -->
    <script>
        const hamburger = document.getElementById('hamburger');
        const navbarMenu = document.getElementById('navbarMenu');
        const profileDropdownParent = document.querySelector('.profile-dropdown');

        hamburger?.addEventListener('click', (e) => {
            // Tutup dropdown profile jika sedang terbuka
            profileDropdownParent?.classList.remove('show');
            // Toggle menu navigasi
            navbarMenu.classList.toggle('show');
            e.stopPropagation();
        });

        profileDropdownParent?.querySelector('.profile-btn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            // Tutup hamburger menu jika sedang terbuka
            navbarMenu.classList.remove('show');
            // Toggle dropdown profile
            profileDropdownParent.classList.toggle('show');
        });

        // Klik di luar akan menutup semua dropdown
        window.onclick = function(event) {
            if (!event.target.closest('.profile-dropdown')) {
                profileDropdownParent?.classList.remove('show');
            }
            if (!event.target.closest('.navbar-toggle') && !event.target.closest('.navbar-nav')) {
                navbarMenu?.classList.remove('show');
            }
        }

        function toggleCart() {
            const cartPopup = document.getElementById('cartPopup');
            cartPopup.classList.toggle('show');
        }
    </script>
</body>
