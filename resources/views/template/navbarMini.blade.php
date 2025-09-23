<!-- Link Font Google -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Link Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<link href="{{ asset('css/navbarMini.css') }}" rel="stylesheet">

<body>
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
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('main.menuMakanan') }}">Menu</a>
        </div>
    </nav>

    <script>
        const hamburger = document.getElementById('hamburger');
        const navbar = document.querySelector('.navbar');

        hamburger.addEventListener('click', () => {
            navbar.classList.toggle('show');
        });
    </script>
</body>

</html>
