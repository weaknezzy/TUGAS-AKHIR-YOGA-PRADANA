<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShippingService
{
    // Koordinat toko dari konfigurasi
    private $storeCoordinates;
    private $ratePerKm;
    private $freeShippingDistance;
    private $zoneRates;
    private $minShippingCost;
    private $maxShippingCost;
    private $cacheDuration;
    private $nominatimConfig;
    private $roadDistanceFactor;

    public function __construct()
    {
        $this->storeCoordinates = config('shipping.store_coordinates');
        $this->ratePerKm = config('shipping.rate_per_km');
        $this->freeShippingDistance = config('shipping.free_shipping_distance', 2.0);
        $this->zoneRates = config('shipping.zone_rates');
        $this->minShippingCost = config('shipping.min_shipping_cost');
        $this->maxShippingCost = config('shipping.max_shipping_cost');
        $this->cacheDuration = config('shipping.cache_duration');
        $this->nominatimConfig = config('shipping.nominatim');
        $this->roadDistanceFactor = config('shipping.road_distance_factor', 1.1);
    }

    /**
     * Hitung ongkos kirim berdasarkan alamat menggunakan OpenStreetMap
     */
    public function calculateShippingCost($address, $latitude = null, $longitude = null)
    {
        try {
            // PRIORITAS 1: Jika koordinat sudah tersedia dari maps
            if ($latitude && $longitude) {
                $coordinates = [
                    'lat' => (float) $latitude,
                    'lng' => (float) $longitude,
                    'display_name' => $address,
                    'source' => 'leaflet_maps'
                ];
                
                // Hitung jarak menggunakan Haversine formula
                $straightDistance = $this->calculateDistance(
                    $this->storeCoordinates['lat'],
                    $this->storeCoordinates['lng'],
                    $coordinates['lat'],
                    $coordinates['lng']
                );

                // Konversi jarak lurus ke jarak jalan (sesuai kondisi Pontianak)
                $roadDistance = $straightDistance * $this->roadDistanceFactor;

                Log::info('Checking free shipping by distance (from maps)', [
                    'address' => $address,
                    'straight_distance_km' => round($straightDistance, 2),
                    'road_distance_km' => round($roadDistance, 2),
                    'free_shipping_threshold' => $this->freeShippingDistance,
                    'is_free_shipping' => $straightDistance <= $this->freeShippingDistance
                ]);

                // Cek apakah gratis ongkir berdasarkan jarak (gunakan straight distance)
                if ($straightDistance <= $this->freeShippingDistance) {
                    Log::info('Free shipping area detected (by distance from maps)', [
                        'address' => $address,
                        'distance_km' => round($straightDistance, 2),
                        'calculation_method' => 'distance-based-free-shipping-from-maps'
                    ]);
                    return 0;
                }

                // Jika tidak gratis, hitung ongkir berdasarkan jarak
                $shippingCost = $this->calculateCostByDistance($roadDistance);

                Log::info('Distance-based shipping calculation (from maps)', [
                    'address' => $address,
                    'store_coordinates' => $this->storeCoordinates,
                    'customer_coordinates' => $coordinates,
                    'straight_distance_km' => round($straightDistance, 2),
                    'road_distance_km' => round($roadDistance, 2),
                    'road_distance_factor' => $this->roadDistanceFactor,
                    'rate_per_km' => $this->ratePerKm,
                    'shipping_cost' => $shippingCost,
                    'calculation_method' => 'distance-based-from-maps'
                ]);

                return $shippingCost;
            }

            // PRIORITAS 2: Jika tidak ada koordinat dari maps, coba geocoding
            $coordinates = $this->geocodeAddressWithOSM($address);

            if ($coordinates) {
                // Hitung jarak menggunakan Haversine formula
                $straightDistance = $this->calculateDistance(
                    $this->storeCoordinates['lat'],
                    $this->storeCoordinates['lng'],
                    $coordinates['lat'],
                    $coordinates['lng']
                );

                // Konversi jarak lurus ke jarak jalan
                $roadDistance = $straightDistance * $this->roadDistanceFactor;

                Log::info('Checking free shipping by distance (from geocoding)', [
                    'address' => $address,
                    'straight_distance_km' => round($straightDistance, 2),
                    'road_distance_km' => round($roadDistance, 2),
                    'free_shipping_threshold' => $this->freeShippingDistance,
                    'is_free_shipping' => $straightDistance <= $this->freeShippingDistance
                ]);

                // Cek apakah gratis ongkir berdasarkan jarak (gunakan straight distance)
                if ($straightDistance <= $this->freeShippingDistance) {
                    Log::info('Free shipping area detected (by distance from geocoding)', [
                        'address' => $address,
                        'distance_km' => round($straightDistance, 2),
                        'calculation_method' => 'distance-based-free-shipping-from-geocoding'
                    ]);
                    return 0;
                }

                // Hitung ongkir berdasarkan jarak jalan
                $shippingCost = $this->calculateCostByDistance($roadDistance);

                Log::info('Distance-based shipping calculation (from geocoding)', [
                    'address' => $address,
                    'store_coordinates' => $this->storeCoordinates,
                    'customer_coordinates' => $coordinates,
                    'straight_distance_km' => round($straightDistance, 2),
                    'road_distance_km' => round($roadDistance, 2),
                    'road_distance_factor' => $this->roadDistanceFactor,
                    'rate_per_km' => $this->ratePerKm,
                    'shipping_cost' => $shippingCost,
                    'calculation_method' => 'distance-based-from-geocoding'
                ]);

                return $shippingCost;
            }

            // PRIORITAS 3: Fallback ke zone-based pricing
            $zoneCost = $this->calculateZoneBasedShipping($address);
            Log::info('Using zone-based pricing as fallback', [
                'address' => $address,
                'zone_cost' => $zoneCost,
                'calculation_method' => 'zone-based-fallback'
            ]);
            return $zoneCost;

        } catch (\Exception $e) {
            Log::error('Error calculating shipping cost', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);

            // Fallback ke zona-based pricing
            return $this->calculateZoneBasedShipping($address);
        }
    }

    /**
     * Geocoding alamat menggunakan OpenStreetMap Nominatim API
     * Khusus untuk area Pontianak dan sekitarnya
     */
    private function geocodeAddressWithOSM($address)
    {
        // Cek cache terlebih dahulu
        $cacheKey = 'osm_geocode_' . md5($address);
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            Log::info('Using cached geocoding result', [
                'address' => $address,
                'cached_coordinates' => $cached
            ]);
            return $cached;
        }

        try {
            // Delay untuk menghormati rate limiting OpenStreetMap (1 request per detik)
            sleep(1);
            
            // Optimasi alamat untuk area Pontianak
            $searchAddress = $this->optimizeAddressForPontianak($address);

            $response = Http::timeout($this->nominatimConfig['timeout'])
                ->withHeaders([
                    'User-Agent' => $this->nominatimConfig['user_agent'],
                    'Accept' => 'application/json',
                    'Accept-Language' => 'id,en'
                ])
                ->get($this->nominatimConfig['base_url'], [
                    'q' => $searchAddress,
                    'format' => 'json',
                    'limit' => $this->nominatimConfig['limit'],
                    'countrycodes' => 'id', // Hanya Indonesia
                    'addressdetails' => 1,
                    'extratags' => 1,
                    'namedetails' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data)) {
                    $result = [
                        'lat' => (float) $data[0]['lat'],
                        'lng' => (float) $data[0]['lon'],
                        'display_name' => $data[0]['display_name'],
                        'osm_id' => $data[0]['osm_id'] ?? null,
                        'osm_type' => $data[0]['osm_type'] ?? null,
                        'place_rank' => $data[0]['place_rank'] ?? null
                    ];

                    // Cache hasil untuk 1 jam
                    Cache::put($cacheKey, $result, $this->cacheDuration);

                    Log::info('OpenStreetMap geocoding successful', [
                        'address' => $address,
                        'optimized_address' => $searchAddress,
                        'coordinates' => $result
                    ]);

                    return $result;
                }
            }

            Log::warning('OpenStreetMap geocoding failed', [
                'address' => $address,
                'optimized_address' => $searchAddress,
                'response_status' => $response->status(),
                'response_body' => substr($response->body(), 0, 200) // Log first 200 chars
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('OpenStreetMap geocoding error', [
                'address' => $address,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Optimasi alamat untuk area Pontianak
     * Memastikan alamat dapat ditemukan dengan baik di OpenStreetMap
     */
    private function optimizeAddressForPontianak($address)
    {
        $address = trim($address);
        
        // Jika alamat sudah mengandung Pontianak, gunakan as is
        if (str_contains(strtolower($address), 'pontianak')) {
            return $address . ', Indonesia';
        }

        // Jika alamat sudah mengandung Kalimantan Barat, gunakan as is
        if (str_contains(strtolower($address), 'kalimantan barat')) {
            return $address;
        }

        // Tambahkan Pontianak jika belum ada
        if (!str_contains(strtolower($address), 'pontianak')) {
            $address .= ', Pontianak';
        }

        // Tambahkan Indonesia untuk hasil yang lebih akurat
        $address .= ', Indonesia';

        return $address;
    }

    /**
     * Hitung jarak menggunakan Haversine formula
     */
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // Radius bumi dalam kilometer

        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDelta / 2) * sin($lngDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Hitung ongkir berdasarkan jarak
     */
    private function calculateCostByDistance($distance)
    {
        // Ongkir berdasarkan jarak
        $rawShippingCost = $distance * $this->ratePerKm;
        
        // Pastikan ongkir dalam range yang wajar
        $shippingCost = max($this->minShippingCost, min($rawShippingCost, $this->maxShippingCost));
        
        Log::info('Distance-based shipping calculation details', [
            'distance_km' => round($distance, 2),
            'rate_per_km' => $this->ratePerKm,
            'raw_shipping_cost' => round($rawShippingCost, 2),
            'min_shipping_cost' => $this->minShippingCost,
            'max_shipping_cost' => $this->maxShippingCost,
            'final_shipping_cost' => $shippingCost,
            'was_limited' => $rawShippingCost !== $shippingCost
        ]);
        
        return round($shippingCost);
    }

    /**
     * Kalkulasi ongkir berdasarkan zona (fallback)
     */
    private function calculateZoneBasedShipping($address)
    {
        $addressLower = strtolower($address);
        
        foreach ($this->zoneRates as $zone => $cost) {
            if (str_contains($addressLower, $zone)) {
                Log::info('Zone-based shipping calculation', [
                    'address' => $address,
                    'matched_zone' => $zone,
                    'shipping_cost' => $cost,
                    'calculation_method' => 'zone-based'
                ]);
                return $cost;
            }
        }
        
        // Default ongkir untuk area yang tidak dikenal
        Log::info('Zone-based shipping calculation (default)', [
            'address' => $address,
            'shipping_cost' => 10000,
            'calculation_method' => 'zone-based-default'
        ]);
        return 10000;
    }

    /**
     * Dapatkan jarak maksimal untuk gratis ongkir
     */
    public function getFreeShippingDistance()
    {
        return $this->freeShippingDistance;
    }

    /**
     * Set koordinat toko
     */
    public function setStoreCoordinates($lat, $lng)
    {
        $this->storeCoordinates = [
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    /**
     * Set tarif per kilometer
     */
    public function setRatePerKm($rate)
    {
        $this->ratePerKm = $rate;
    }

    /**
     * Set faktor koreksi jarak jalan
     */
    public function setRoadDistanceFactor($factor)
    {
        $this->roadDistanceFactor = $factor;
    }

    /**
     * Test koneksi ke OpenStreetMap
     */
    public function testOSMConnection()
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => $this->nominatimConfig['user_agent'],
                    'Accept' => 'application/json'
                ])
                ->get($this->nominatimConfig['base_url'], [
                    'q' => 'Pontianak, Indonesia',
                    'format' => 'json',
                    'limit' => 1
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
} 