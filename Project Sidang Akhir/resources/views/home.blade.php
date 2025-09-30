<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home Page</title>

    <!-- Link CSS -->
    <link rel="stylesheet" href="{{ asset('/css/styleHome.css') }}">
    <!-- Tambahkan CSS navbarMenu agar navbar sama persis -->
    <link rel="stylesheet" href="{{ asset('css/navbarMenu.css') }}">

    <!-- Link Font Google -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

</head>

<body>
    <!-- Navbar Start -->
    <nav class="navbar">
        <a href="#" class="navbar-logo">
            <img src="/images/logo.png" alt="Logo Pakde Along" class="navbar-logo-img">
            <div class="navbar-logo-text">
                Warung Makan <span>Rumah Pakde Along</span>
            </div>
        </a>

        <div class="navbar-toggle" id="hamburger">
            <i class="fas fa-bars"></i>
        </div>

        <div class="navbar-nav" id="navbarMenu">
            <a href="#">Home</a>
            <a href="{{ route('main.menuCatering') }}">Catering Menu</a>
            <a href="#about">Tentang Kami</a>
            <a href="#contact">Kontak</a>
        </div>

        <!-- Bagian Profil Dropdown -->
        <div class="profile-section">
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
    <!-- Navbar End -->

    <!-- Notifikasi Login Berhasil -->
    @if (session('login_success') && !empty($welcomeMessage) && str_contains($welcomeMessage, 'Selamat datang kembali') && !session('login_notification_shown'))
        <div class="login-notification">
            <div class="notification-container">
                <div class="notification-background"></div>
                
                <div class="notification-content">
                    <div class="icon-container">
                        <div class="icon-wrapper">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="decorative-dot"></div>
                    </div>
                    
                    <div class="text-content">
                        <h4 class="notification-title">
                            <span class="emoji">🎉</span>
                            <span>Selamat Datang!</span>
                        </h4>
                        <p class="notification-message">{{ $welcomeMessage }}</p>
                        <p class="notification-subtitle">
                            <span class="emoji">✨</span>
                            <span>Selamat menikmati pengalaman berbelanja Anda</span>
                        </p>
                    </div>
                </div>
                
                <button onclick="closeLoginNotification()" class="close-button">
                    <i class="fas fa-times"></i>
                </button>
                
                <div class="decorative-border"></div>
            </div>
        </div>
    @endif

    <div class="container">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-left">
                <h1>Nikmati Hidangan <span>Rumahan</span></h1>
                <p>Pesan Makanan Harian dan<span> layanan catering dengan mudah</span></p>
                <a href="{{ route('main.menuMakanan') }}" class="btn">Lihat Menu</a>
            </div>
            <div class="hero-right">
                <img src="/images/logo.png" alt="Logo Pakde Along">
            </div>
        </section>

        <!-- About Section -->
        <section class="about-section" id="about">
            <div class="about-image">
                <img src="/images/Toko.png" alt="Foto Warung Pakde Along">
            </div>
            <div class="about-text">
                <h2><span>Tentang</span> Kami</h2>
                <p>Rumah Makan Pakde Along telah berdiri sejak 2020, berlokasi di Jalan Kom Yos Sudarso No. 205, di
                    samping Rumah Sakit Sultan Syarif Abdurrahman. Kami menyajikan masakan rumahan dengan harga
                    terjangkau dan menawarkan menu yang konsisten setiap harinya.</p>
                <p>Didukung oleh 8 karyawan dan 3 juru masak berpengalaman, kami berkomitmen memberikan layanan terbaik.
                    Kami juga menyediakan layanan antar gratis untuk wilayah Jeruju, Jalan Karet, dan Jalan TPI, serta
                    biaya tambahan Rp10.000 untuk wilayah di luar area tersebut.</p>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="contact-section" id="contact">
            <div class="map">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.8182835504235!2d109.29467366874977!3d-0.007781118873350034!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e1d58af8cb12151%3A0x67c55d3ab6d3ee4!2sWarung%20Makan%20Pakde%20Along!5e0!3m2!1sid!2sid!4v1751029647010!5m2!1sid!2sid"
                    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
            <div class="contact-info">
                <h2><span>Kontak</span> Kami</h2>
                <div class="contact-box">
                    <i class="fas fa-phone"></i>
                    0895613483990
                </div>
                <div class="contact-box">
                    <i class="fas fa-envelope"></i>
                    pakdeAlong@gmail.com
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-social">
            <a href="#"><i class="fab fa-whatsapp"></i></a>
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
        <div class="footer-credit">
            created by <span>yogapradana</span> | &copy 2025
        </div>
    </footer>

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

        // Fungsi untuk menutup notifikasi login berhasil
        function closeLoginNotification() {
            const notification = document.querySelector('.login-notification');
            if (notification) {
                notification.remove();
            }
        }

        // Auto-hide notifikasi login berhasil setelah 8 detik
        const notification = document.querySelector('.login-notification');
        if (notification) {
            // Tandai bahwa notifikasi sudah ditampilkan
            sessionStorage.setItem('login_notification_shown', 'true');
            
            setTimeout(() => {
                closeLoginNotification();
            }, 8000);
        }
    </script>
</body>

</html>
