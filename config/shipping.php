<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Store Coordinates (Koordinat Toko)
    |--------------------------------------------------------------------------
    |
    | Koordinat lokasi toko untuk kalkulasi jarak
    | Ganti dengan koordinat toko Anda yang sebenarnya
    |
    */
    'store_coordinates' => [
        'lat' => env('STORE_LATITUDE', -0.00781), // Latitude toko Anda
        'lng' => env('STORE_LONGITUDE', 109.29478), // Longitude toko Anda
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Rates (Tarif Ongkir)
    |--------------------------------------------------------------------------
    |
    | Tarif ongkos kirim per kilometer
    |
    */
    'rate_per_km' => env('SHIPPING_RATE_PER_KM', 3000), // Rp 3.000 per km

    /*
    |--------------------------------------------------------------------------
    | Road Distance Factor (Faktor Koreksi Jarak Jalan)
    |--------------------------------------------------------------------------
    |
    | Faktor koreksi untuk mengkonversi jarak lurus ke jarak jalan
    | Google Maps biasanya 1.2-1.5x lebih jauh dari jarak lurus
    | Sesuaikan berdasarkan kondisi jalan di Pontianak
    |
    */
    'road_distance_factor' => env('ROAD_DISTANCE_FACTOR', 1.1), // 1.1x = 10% lebih jauh (lebih akurat)

    /*
    |--------------------------------------------------------------------------
    | Free Shipping Distance (Jarak Gratis Ongkir)
    |--------------------------------------------------------------------------
    |
    | Jarak maksimal untuk gratis ongkir dari toko (dalam kilometer)
    |
    */
    'free_shipping_distance' => env('FREE_SHIPPING_DISTANCE', 2.0), // 2.0 km = gratis ongkir

    /*
    |--------------------------------------------------------------------------
    | Zone-based Shipping Rates (Tarif Berdasarkan Zona)
    |--------------------------------------------------------------------------
    |
    | Tarif ongkir berdasarkan zona di Pontianak dan sekitarnya
    | Digunakan sebagai fallback jika geocoding gagal
    |
    */
    'zone_rates' => [
        // Area dalam kota Pontianak
        'pontianak kota' => 5000,
        'pontianak tenggara' => 5000,
        'pontianak barat' => 8000,
        'pontianak utara' => 8000,
        'pontianak timur' => 8000,
        'pontianak selatan' => 8000,
        
        // Area spesifik Pontianak
        'kota baru' => 17400, // 8.7 km × Rp 2.000
        'sungai bangkong' => 6000,
        'jeruju' => 0, // Gratis ongkir
        'bansir laut' => 8000, // Sesuai jarak dari toko
        'bansir darat' => 8000, // Sesuai jarak dari toko
        'benua melayu' => 8000,
        'akcaya' => 6000,
        'siantan' => 9000,
        'pal lima' => 10000,
        'pal tujuh' => 11000,
        
        // Area sekitar Pontianak
        'kubu raya' => 12000,
        'sungai raya' => 15000,
        'sungai kakap' => 18000,
        'teluk pakedai' => 20000,
        'mempawah' => 25000,
        'landak' => 30000,
        'sambas' => 35000,
        'bengkayang' => 40000,
        'singkawang' => 45000,
        'ketapang' => 50000,
        'sanggau' => 55000,
        'sekadau' => 60000,
        'sintang' => 65000,
        'kapuas hulu' => 70000,
        'melawi' => 75000,
        'kayong utara' => 80000
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Limits (Batasan Ongkir)
    |--------------------------------------------------------------------------
    |
    | Batasan minimum dan maksimum ongkir
    |
    */
    'min_shipping_cost' => 2000, // Minimum ongkir (sesuai tarif per km)
    'max_shipping_cost' => 50000, // Maksimum ongkir

    /*
    |--------------------------------------------------------------------------
    | Cache Settings (Pengaturan Cache)
    |--------------------------------------------------------------------------
    |
    | Pengaturan cache untuk geocoding OpenStreetMap
    |
    */
    'cache_duration' => 3600, // 1 jam dalam detik

    /*
    |--------------------------------------------------------------------------
    | OpenStreetMap Nominatim API Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk OpenStreetMap Nominatim API
    | Khusus untuk area Pontianak dan sekitarnya
    |
    */
    'nominatim' => [
        'base_url' => 'https://nominatim.openstreetmap.org/search',
        'timeout' => 10, // Timeout dalam detik
        'country_codes' => 'id', // Hanya Indonesia
        'limit' => 1, // Jumlah hasil maksimal
        'user_agent' => 'TA_Catering_App/1.0 (https://your-domain.com; contact@your-domain.com)',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Alternative Geocoding APIs (API Alternatif)
    |--------------------------------------------------------------------------
    |
    | API alternatif jika OpenStreetMap gagal
    |
    */
    'alternative_apis' => [
        'photon' => [
            'base_url' => 'https://photon.komoot.io/api/',
            'timeout' => 5,
        ],
        'locationiq' => [
            'base_url' => 'https://us1.locationiq.com/v1/search.php',
            'timeout' => 5,
            'key' => env('LOCATIONIQ_API_KEY', ''), // Optional API key
        ],
    ],
]; 