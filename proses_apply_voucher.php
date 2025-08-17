<?php
// /proses_apply_voucher.php (Versi Final dengan Dukungan "Beli Sekarang")
require_once 'config/database.php';
require_once 'includes/functions.php'; // Penting untuk memanggil fungsi harga

header('Content-Type: application/json');

$response = [
    'success' => false,
        'message' => 'Terjadi kesalahan tidak dikenal.',
            'discount_amount' => 0,
                'applied_code' => ''
                ];

                try {
                    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
                            throw new Exception('Akses tidak sah.');
                                }

                                    // --- PERBAIKAN: Tentukan item yang akan diproses dari session yang benar ---
                                        $is_buy_now = isset($_SESSION['buy_now_item']) && !empty($_SESSION['buy_now_item']);
                                            $items_to_process = [];

                                                if ($is_buy_now) {
                                                        $items_to_process[$_SESSION['buy_now_item']['produk_id']] = $_SESSION['buy_now_item']['jumlah'];
                                                            } elseif (!empty($_SESSION['keranjang'])) {
                                                                    $items_to_process = $_SESSION['keranjang'];
                                                                        } else {
                                                                                throw new Exception('Keranjang belanja kosong.');
                                                                                    }
                                                                                        // --- AKHIR PERBAIKAN ---

                                                                                            $user_id = $_SESSION['user_id'];
                                                                                                $kode_voucher = trim($_POST['kode_voucher'] ?? '');

                                                                                                    if (empty($kode_voucher)) {
                                                                                                            throw new Exception('Kode voucher tidak boleh kosong.');
                                                                                                                }

                                                                                                                    // 1. Ambil detail voucher
                                                                                                                        $stmt_voucher = $koneksi->prepare("SELECT * FROM voucher WHERE kode = ?");
                                                                                                                            $stmt_voucher->bind_param("s", $kode_voucher);
                                                                                                                                $stmt_voucher->execute();
                                                                                                                                    $voucher = $stmt_voucher->get_result()->fetch_assoc();
                                                                                                                                        $stmt_voucher->close();

                                                                                                                                            if (!$voucher) {
                                                                                                                                                    throw new Exception('Kode voucher tidak ditemukan.');
                                                                                                                                                        }

                                                                                                                                                            // 2. Validasi status dan tanggal
                                                                                                                                                                $now = new DateTime();
                                                                                                                                                                    if (!$voucher['is_active'] || new DateTime($voucher['tanggal_mulai']) > $now || new DateTime($voucher['tanggal_akhir']) < $now) {
                                                                                                                                                                            throw new Exception('Voucher tidak aktif atau sudah kadaluarsa.');
                                                                                                                                                                                }

                                                                                                                                                                                    // 3. Hitung subtotal produk dari toko yang relevan
                                                                                                                                                                                        $ids_to_fetch = array_keys($items_to_process);
                                                                                                                                                                                            if (empty($ids_to_fetch)) {
                                                                                                                                                                                                     throw new Exception('Tidak ada item untuk divalidasi.');
                                                                                                                                                                                                         }
                                                                                                                                                                                                             $ids_string = implode(',', array_map('intval', $ids_to_fetch));
                                                                                                                                                                                                                 $query_produk = "SELECT id, harga, harga_diskon, promo_mulai, promo_akhir, toko_id FROM produk WHERE id IN ($ids_string)";
                                                                                                                                                                                                                     $result_produk = mysqli_query($koneksi, $query_produk);
                                                                                                                                                                                                                         
                                                                                                                                                                                                                             $eligible_subtotal = 0;
                                                                                                                                                                                                                                 
                                                                                                                                                                                                                                     while ($prod_data = mysqli_fetch_assoc($result_produk)) {
                                                                                                                                                                                                                                             if ($prod_data['toko_id'] == $voucher['toko_id']) {
                                                                                                                                                                                                                                                         $jumlah = $items_to_process[$prod_data['id']];
                                                                                                                                                                                                                                                                     $promo_data = getEffectivePriceAndPromoStatus($prod_data);
                                                                                                                                                                                                                                                                                 $eligible_subtotal += $promo_data['price'] * $jumlah;
                                                                                                                                                                                                                                                                                         }
                                                                                                                                                                                                                                                                                             }
                                                                                                                                                                                                                                                                                                 
                                                                                                                                                                                                                                                                                                     if($eligible_subtotal <= 0){
                                                                                                                                                                                                                                                                                                             throw new Exception('Voucher ini tidak berlaku untuk produk di keranjang Anda.');
                                                                                                                                                                                                                                                                                                                 }
                                                                                                                                                                                                                                                                                                                     
                                                                                                                                                                                                                                                                                                                         // 4. Validasi sisa
                                                                                                                                                                                                                                                                                                                             if ($voucher['min_pembelian'] !== null && $eligible_subtotal < $voucher['min_pembelian']) {
                                                                                                                                                                                                                                                                                                                                     throw new Exception('Minimum pembelian untuk voucher ini adalah Rp ' . number_format($voucher['min_pembelian']));
                                                                                                                                                                                                                                                                                                                                         }
                                                                                                                                                                                                                                                                                                                                             if ($voucher['jumlah_penggunaan_total'] !== null && $voucher['jumlah_digunakan_saat_ini'] >= $voucher['jumlah_penggunaan_total']) {
                                                                                                                                                                                                                                                                                                                                                     throw new Exception('Voucher sudah habis kuota penggunaan.');
                                                                                                                                                                                                                                                                                                                                                         }
                                                                                                                                                                                                                                                                                                                                                             
                                                                                                                                                                                                                                                                                                                                                                 // 5. Hitung Diskon
                                                                                                                                                                                                                                                                                                                                                                     $discount_amount = ($eligible_subtotal * $voucher['nilai_diskon']) / 100;

                                                                                                                                                                                                                                                                                                                                                                         $response['success'] = true;
                                                                                                                                                                                                                                                                                                                                                                             $response['message'] = 'Voucher berhasil diterapkan!';
                                                                                                                                                                                                                                                                                                                                                                                 $response['discount_amount'] = round($discount_amount);
                                                                                                                                                                                                                                                                                                                                                                                     $response['applied_code'] = $kode_voucher;

                                                                                                                                                                                                                                                                                                                                                                                     } catch (Exception $e) {
                                                                                                                                                                                                                                                                                                                                                                                         $response['message'] = $e->getMessage();
                                                                                                                                                                                                                                                                                                                                                                                         }

                                                                                                                                                                                                                                                                                                                                                                                         echo json_encode($response);
                                                                                                                                                                                                                                                                                                                                                                                         if (isset($koneksi)) $koneksi->close();
                                                                                                                                                                                                                                                                                                                                                                                         ?>