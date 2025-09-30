<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $menu->nama_menu }} - TA Catering</title>
    
    <!-- Meta tags untuk SEO -->
    <meta name="description" content="{{ $menu->deskripsi ?? 'Lihat detail menu ' . $menu->nama_menu . ' dengan harga Rp ' . number_format($menu->harga, 0, ',', '.') }}">
    <meta name="keywords" content="menu, makanan, {{ $menu->nama_menu }}, catering, TA Catering">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ $menu->nama_menu }}">
    <meta property="og:description" content="{{ $menu->deskripsi ?? 'Lihat detail menu ' . $menu->nama_menu . ' dengan harga Rp ' . number_format($menu->harga, 0, ',', '.') }}">
    <meta property="og:image" content="{{ asset('storage/' . $menu->gambar) }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="TA Catering">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $menu->nama_menu }}">
    <meta name="twitter:description" content="{{ $menu->deskripsi ?? 'Lihat detail menu ' . $menu->nama_menu . ' dengan harga Rp ' . number_format($menu->harga, 0, ',', '.') }}">
    <meta name="twitter:image" content="{{ asset('storage/' . $menu->gambar) }}">
    
    <!-- Additional Meta Tags -->
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <link href="{{ asset('css/cart.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">

    <style>
        .menu-detail-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .menu-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .menu-title {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .menu-price {
            font-size: 1.5em;
            color: #388e3c;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .menu-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .menu-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .btn-keranjang {
            background: #388e3c;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-keranjang:hover {
            background: #2e7d32;
        }
        

        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f5f5f5;
            color: #333;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
            transition: background 0.3s ease;
        }
        
        .back-button:hover {
            background: #e0e0e0;
        }

        @media (max-width: 768px) {
            .menu-detail-container {
                margin: 10px;
                padding: 15px;
            }
            
            .menu-title {
                font-size: 1.5em;
            }
            
            .menu-price {
                font-size: 1.2em;
            }
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    @include('template.navbarMainMenu')

    <main class="container mx-auto my-6">
        <div class="menu-detail-container">
            <a href="{{ url()->previous() }}" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
            
            <img src="{{ asset('storage/' . $menu->gambar) }}" alt="{{ $menu->nama_menu }}" class="menu-image">
            
            <h1 class="menu-title">{{ $menu->nama_menu }}</h1>
            <div class="menu-price">Rp {{ number_format($menu->harga, 0, ',', '.') }}</div>
            
            @if($menu->deskripsi)
                <div class="menu-description">{{ $menu->deskripsi }}</div>
            @endif
            
            <div class="menu-actions">
                <form action="{{ route('keranjang.add') }}" method="POST">
                    @csrf
                    <input type="hidden" name="menu_id" value="{{ $menu->id }}">
                    <input type="hidden" name="jumlah" value="1">
                    <button type="submit" class="btn-keranjang">
                        <i class="fas fa-shopping-cart"></i>
                        Tambah ke Keranjang
                    </button>
                </form>
            </div>
            

        </div>
    </main>

    @include('template.footer')

    @stack('scripts')
</body>

</html>
