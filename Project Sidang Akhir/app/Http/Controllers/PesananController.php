<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Pemesanan;
use App\Models\Keranjang;
use App\Models\Laporan;
use App\Services\ShippingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Snap;
use Midtrans\Config;

class PesananController extends Controller
{
    private $shippingService;

    public function __construct(ShippingService $shippingService)
    {
        $this->shippingService = $shippingService;
    }

    public function store(Request $request)
    {
        // \Log::info('Store pesanan - request data', $request->all());
        try{
            // validasi data - customer_name dan phone selalu required
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'email' => 'nullable|email',
                'no_telp' => 'required|string|max:20',
                'order_items' => 'required|string', // JSON
                'note' => 'nullable|string',
                'total_amount' => 'required|numeric|min:1',
                'payment_method' => 'required|in:COD,Transfer',
                'alamat' => 'required|string', // tambahan validasi untuk alamat
                'ongkir' => 'required|numeric|min:0', // tambahan validasi untuk ongkir
                'order_id' => 'required|string',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
            ]);

            // Validasi keranjang tidak kosong
            $orderItems = json_decode($validated['order_items'], true);
            if (empty($orderItems) || !is_array($orderItems)) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Keranjang kosong. Silakan pilih menu terlebih dahulu.'
                ], 400);
            }

            // Validasi total amount harus lebih dari 0
            if ($validated['total_amount'] <= 0) {
                return response()->json([
                    'success' => false, 
                    'error' => 'Total belanja tidak valid. Silakan pilih menu terlebih dahulu.'
                ], 400);
            }

            // Validasi keranjang user tidak kosong di database
            if (Auth::check()) {
                $cartItems = Keranjang::where('id_user', Auth::user()->id_user)->count();
                if ($cartItems === 0) {
                    return response()->json([
                        'success' => false, 
                        'error' => 'Keranjang kosong. Silakan pilih menu terlebih dahulu.'
                    ], 400);
                }
            } else {
                $cartItems = Keranjang::where('session_id', session()->getId())->count();
                if ($cartItems === 0) {
                    return response()->json([
                        'success' => false, 
                        'error' => 'Keranjang kosong. Silakan pilih menu terlebih dahulu.'
                    ], 400);
                }
            }
            \Log::info('Store pesanan - validated data', $validated);

            // Verifikasi ongkir
            $calculatedShipping = $this->shippingService->calculateShippingCost(
                $validated['alamat'],
                $request->latitude,
                $request->longitude
            );
            if ($calculatedShipping != $validated['ongkir']) {
                return redirect()->back()->with('error', 'Perhitungan ongkir tidak valid. Silakan coba lagi.');
            }

            // Gunakan order_id yang sudah ada dari frontend
            $order_id = $validated['order_id'];
            \Log::info('Store pesanan - using order_id from frontend: ' . $order_id);
            $validated['status'] = 'Pending';

            // jika user login, tambahkan user_id dan email dari user
            if (Auth::check()) {
                $validated['user_id'] = Auth::user()->id_user;
                $validated['email'] = Auth::user()->email;
            }

            // simpan pemesanan
            $pemesanan = Pemesanan::create($validated);

            // Simpan ke tabel laporan untuk monitoring admin
            Laporan::create([
                'user_id' => Auth::check() ? Auth::user()->id_user : null,
                'order_id' => $order_id, // gunakan order_id string, bukan id integer
                'customer_name' => $validated['customer_name'],
                'no_telp' => $validated['no_telp'],
                'order_items' => $validated['order_items'],
                'note' => $validated['note'] ?? null,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'status' => 'Pending',
            ]);

            // Simpan nomor HP ke session jika guest (tidak login)
            if (!Auth::check() && isset($validated['no_telp'])) {
                session(['guest_phone' => $validated['no_telp']]);
            }
            // Simpan nama guest ke session jika guest (tidak login)
            if (!Auth::check() && isset($validated['customer_name'])) {
                session(['guest_name' => $validated['customer_name']]);
            }
            
            // Set flag untuk menandai bahwa pemesanan berhasil (untuk guest)
            if (!Auth::check()) {
                session(['order_success' => true]);
            }

            // kosongkan keranjang
            if (Auth::check()) {
                Keranjang::where('id_user', Auth::user()->id_user)->delete();
            } else {
                Keranjang::where('session_id', session()->getId())->delete();
            }

            \Log::info('Store pesanan - sukses', ['order_id' => $order_id]);
            return response()->json(['success' => true, 'order_id' => $order_id, 'frontend_order_id' => $validated['order_id']]);
        }catch (\Exception $e) {
            \Log::error('Error saat store pesanan: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Terjadi kesalahan. coba lagi']);
        }

    }

    public function getOrderDetail($orderId)
    {
        // Cari pesanan berdasarkan order_id (string), bukan id (integer)
        $order = Pemesanan::where('order_id', $orderId)->first();
        
        if (!$order) {
            return response()->json(['error' => 'Pesanan tidak ditemukan'], 404);
        }

        // Validasi keamanan: user hanya bisa melihat pesanan mereka sendiri
        if (Auth::check()) {
            // User login: cek apakah pesanan milik user ini
            if ($order->user_id !== Auth::user()->id_user) {
                return response()->json(['error' => 'Anda tidak memiliki akses ke pesanan ini'], 403);
            }
        } else {
            // Guest: cek apakah pesanan sesuai dengan no_telp di session atau request
            $guestPhone = session('guest_phone');
            $requestPhone = request('no_telp');
            
            if (!$guestPhone && !$requestPhone) {
                return response()->json(['error' => 'Anda tidak memiliki akses ke pesanan ini'], 403);
            }
            
            if ($requestPhone && $order->no_telp !== $requestPhone) {
                return response()->json(['error' => 'Anda tidak memiliki akses ke pesanan ini'], 403);
            }
            
            if ($guestPhone && $order->no_telp !== $guestPhone) {
                return response()->json(['error' => 'Anda tidak memiliki akses ke pesanan ini'], 403);
            }
        }

        // Decode order_items dari JSON dan standardize format
        $orderItems = json_decode($order->order_items, true);
        
        // Convert format jika diperlukan (dari 'harga'/'jumlah' ke 'price'/'quantity')
        if (is_array($orderItems)) {
            $standardizedItems = [];
            foreach ($orderItems as $item) {
                $standardizedItems[] = [
                    'name' => $item['name'] ?? $item['nama_menu'] ?? 'Item',
                    'price' => $item['harga'] ?? $item['price'] ?? 0,
                    'quantity' => $item['jumlah'] ?? $item['quantity'] ?? 0
                ];
            }
            $orderItems = $standardizedItems;
        }

        // Hitung subtotal (total sebelum ongkir)
        $subtotal = $order->total_amount - $order->ongkir;
        
        return response()->json([
            'order' => $order,
            'order_items' => $orderItems,
            'subtotal' => $subtotal,
            'shipping_cost' => $order->ongkir,
            'total_with_shipping' => $order->total_amount,
            'shipping_address' => $order->alamat
        ]);
    }

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'alamat' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        $shippingCost = $this->shippingService->calculateShippingCost(
            $request->alamat,
            $request->latitude,
            $request->longitude
        );
        
        return response()->json([
            'shipping_cost' => $shippingCost,
            'free_shipping_distance' => $this->shippingService->getFreeShippingDistance()
        ]);
    }

    /**
     * Generate Snap Token untuk pembayaran Midtrans
     */
    public function getSnapToken(Request $request)
    {
        \Log::info('getSnapToken called', $request->all());
        
        $request->validate([
            'order_id' => 'required|string', // pastikan order_id dikirim dari frontend
            'total_amount' => 'required|numeric|min:1',
            'customer_name' => 'required|string',
            'no_telp' => 'required|string',
        ]);

        // Cari order di database
        $order = \App\Models\Pemesanan::where('order_id', $request->order_id)->first();
        \Log::info('SnapToken - searching for order_id: ' . $request->order_id);
        if (!$order) {
            \Log::error('SnapToken - order not found for order_id: ' . $request->order_id);
            return response()->json(['error' => 'Order not found'], 404);
        }
        \Log::info('SnapToken - order found in database: ' . $order->order_id);

        // Konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        \Log::info('Midtrans config', [
            'server_key' => config('midtrans.server_key'),
            'is_production' => config('midtrans.is_production'),
            'client_key' => config('midtrans.client_key')
        ]);

        $alamat = Auth::check() ? (Auth::user()->alamat ?: '') : ($order->alamat ?: '');
        $customerDetails = [
            'first_name' => $order->customer_name,
            'phone' => $order->no_telp,
        ];
        $email = Auth::check() ? (Auth::user()->email ?: null) : ($order->email ?: null);
        if ($email) {
            $customerDetails['email'] = $email;
        }
        $customerDetails['billing_address'] = [
            'first_name' => $order->customer_name,
            'phone' => $order->no_telp,
            'address' => $alamat,
        ];
        $customerDetails['shipping_address'] = [
            'first_name' => $order->customer_name,
            'phone' => $order->no_telp,
            'address' => $alamat,
        ];
        $params = [
            'transaction_details' => [
                'order_id' => $order->order_id, // gunakan order_id dari database
                'gross_amount' => (int) $order->total_amount, // Convert ke integer
            ],
            'customer_details' => $customerDetails
        ];

        \Log::info('SnapToken - order_id from request: ' . $request->order_id);
        \Log::info('SnapToken - order_id from database: ' . $order->order_id);
        \Log::info('SnapToken - params', $params);
        try {
            $snapToken = Snap::getSnapToken($params);
            \Log::info('SnapToken - success', ['snap_token' => $snapToken]);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            \Log::error('SnapToken - error: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $params,
                'order_id' => $request->order_id
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Admin mengkonfirmasi pembayaran pesanan
     */
    public function confirmPayment($orderId)
    {
        // Pastikan hanya admin yang bisa mengkonfirmasi pembayaran
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'error' => 'Unauthorized. Hanya admin yang dapat mengkonfirmasi pembayaran.'
            ], 403);
        }

        $order = Pemesanan::findOrFail($orderId);
        
        if ($order->status !== 'Pending') {
            return response()->json([
                'error' => 'Pesanan tidak dalam status Pending'
            ], 400);
        }

        $order->status = 'Diproses';
        $order->save();

        // Update juga di tabel laporan
        Laporan::where('order_id', $orderId)->update(['status' => 'Diproses']);

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi',
            'order' => $order
        ]);
    }

    /**
     * Admin menolak pembayaran pesanan
     */
    public function rejectPayment($orderId)
    {
        // Pastikan hanya admin yang bisa menolak pembayaran
        if (Auth::user()->role !== 'admin') {
            return response()->json([
                'error' => 'Unauthorized. Hanya admin yang dapat menolak pembayaran.'
            ], 403);
        }

        $order = Pemesanan::findOrFail($orderId);
        
        if ($order->status !== 'Pending') {
            return response()->json([
                'error' => 'Pesanan tidak dalam status Pending'
            ], 400);
        }

        $order->status = 'Dibatalkan';
        $order->save();

        // Update juga di tabel laporan
        Laporan::where('order_id', $orderId)->update(['status' => 'Dibatalkan']);

        return response()->json([
            'message' => 'Pembayaran ditolak',
            'order' => $order
        ]);
    }

    /**
     * Pelanggan mengkonfirmasi pesanan telah diterima
     */
    public function confirmDelivery($orderId, Request $request)
    {
        try {
            DB::beginTransaction();

            // Cari pesanan berdasarkan order_id (string), bukan id (integer)
            $order = Pemesanan::where('order_id', $orderId)->firstOrFail();

            // Validasi akses
            if (Auth::check()) {
                if ($order->user_id !== Auth::user()->id_user) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Anda tidak memiliki akses ke pesanan ini',
                        'status' => 'error'
                    ], 403);
                }
            } else {
                if (empty($request->no_telp) || $order->no_telp !== $request->no_telp) {
                    DB::rollBack();
                    return response()->json([
                        'error' => 'Nomor telepon tidak sesuai dengan pesanan',
                        'status' => 'error'
                    ], 403);
                }
            }

            if ($order->status !== 'Diproses') {
                DB::rollBack();
                return response()->json([
                    'error' => 'Pesanan tidak dalam status Diproses',
                    'status' => 'error'
                ], 400);
            }

            // Update status di tabel Laporan berdasarkan order_id (string)
            Laporan::where('order_id', $order->order_id)
                ->update(['status' => 'Selesai']);

            // Hapus pesanan setelah status laporan diupdate
            $order->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pesanan telah dikonfirmasi selesai',
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Terjadi kesalahan saat memproses konfirmasi',
                'status' => 'error'
            ], 500);
        }
    }

    /**
     * Handle Midtrans notification/callback
     */
    public function notificationHandler(Request $request)
    {
        // Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        // Logging untuk debugging
        \Log::info('Midtrans notification received', $request->all());

        try {
            // Ambil data dari request
            $order_id = $request->input('order_id');
            $transaction = $request->input('transaction_status');
            $fraud = $request->input('fraud_status');
            $payment_type = $request->input('payment_type');
            $gross_amount = $request->input('gross_amount');

            // Log semua data yang diterima
            \Log::info('Midtrans notification data', [
                'order_id' => $order_id,
                'transaction_status' => $transaction,
                'fraud_status' => $fraud,
                'payment_type' => $payment_type,
                'gross_amount' => $gross_amount,
                'all_data' => $request->all()
            ]);

            // Validasi data yang diperlukan
            if (!$order_id) {
                \Log::error('order_id not found in notification');
                return response()->json(['error' => 'order_id not found'], 400);
            }

            if (!$transaction) {
                \Log::error('transaction_status not found in notification');
                return response()->json(['error' => 'transaction_status not found'], 400);
            }

            // Cari pesanan berdasarkan order_id
            $pemesanan = \App\Models\Pemesanan::where('order_id', $order_id)->first();
            \Log::info('Searching for order_id: ' . $order_id);
            
            if (!$pemesanan) {
                \Log::error('Order not found: ' . $order_id);
                return response()->json(['message' => 'Order not found'], 404);
            }
            
            \Log::info('Order found', [
                'order_id' => $pemesanan->order_id,
                'current_status' => $pemesanan->status,
                'customer_name' => $pemesanan->customer_name,
                'total_amount' => $pemesanan->total_amount
            ]);

            // Mapping status Midtrans ke status pesanan
            $oldStatus = $pemesanan->status;
            $newStatus = null;
            
            if ($transaction == 'capture' || $transaction == 'settlement') {
                $newStatus = 'Diproses';
                \Log::info('Payment successful - updating status to Diproses');
            } elseif ($transaction == 'pending') {
                $newStatus = 'Pending';
                \Log::info('Payment pending - keeping status as Pending');
            } elseif ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
                $newStatus = 'Dibatalkan';
                \Log::info('Payment failed/cancelled - updating status to Dibatalkan');
            } else {
                \Log::warning('Unknown transaction status: ' . $transaction);
                return response()->json(['message' => 'Unknown transaction status'], 400);
            }

            // Update status pesanan
            if ($newStatus && $oldStatus !== $newStatus) {
                $pemesanan->status = $newStatus;
                $pemesanan->save();
                
                \Log::info('Order status updated', [
                    'order_id' => $order_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);

                // Update juga di tabel laporan
                $laporan = \App\Models\Laporan::where('order_id', $order_id)->first();
                if ($laporan) {
                    $laporan->status = $newStatus;
                    $laporan->save();
                    \Log::info('Laporan status also updated', [
                        'order_id' => $order_id,
                        'new_status' => $newStatus
                    ]);
                } else {
                    \Log::warning('Laporan not found for order_id: ' . $order_id);
                }
            } else {
                \Log::info('Status unchanged', [
                    'order_id' => $order_id,
                    'current_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
            }

            \Log::info('Notification processed successfully', [
                'order_id' => $order_id,
                'transaction_status' => $transaction,
                'fraud_status' => $fraud,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
            
            return response()->json([
                'message' => 'Notification processed successfully',
                'order_id' => $order_id,
                'transaction_status' => $transaction,
                'status_updated' => $oldStatus !== $newStatus
            ]);

        } catch (\Exception $e) {
            \Log::error('Error processing Midtrans notification: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
