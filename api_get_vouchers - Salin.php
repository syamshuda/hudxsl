<?php
// /api_get_vouchers.php (Versi Final Lengkap dan Fungsional)
require_once 'config/database.php';
header('Content-Type: application/json');

$response = ['vouchers' => []];

// 1. Proteksi: Pastikan pengguna sudah login sebagai pembeli
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    http_response_code(401); // Unauthorized
    echo json_encode(['message' => 'Harap login sebagai pembeli.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$subtotal_produk = isset($_GET['subtotal']) ? (float)$_GET['subtotal'] : 0;
$biaya_ongkir = isset($_GET['ongkir']) ? (float)$_GET['ongkir'] : 0;
$toko_ids_in_cart = [];

// 2. Tentukan item yang ada di keranjang (mendukung "Beli Sekarang" dan keranjang biasa)
$items_in_cart = $_SESSION['keranjang'] ?? ($_SESSION['buy_now_item'] ? [$_SESSION['buy_now_item']['produk_id'] => $_SESSION['buy_now_item']['jumlah']] : []);

if (empty($items_in_cart)) {
    echo json_encode($response); // Kirim array kosong jika tidak ada item
    exit();
}

// 3. Dapatkan ID toko dari produk yang ada di keranjang
$ids_string = implode(',', array_map('intval', array_keys($items_in_cart)));
$result_toko = mysqli_query($koneksi, "SELECT DISTINCT toko_id FROM produk WHERE id IN ($ids_string)");
if ($result_toko) {
    while ($row = mysqli_fetch_assoc($result_toko)) {
        $toko_ids_in_cart[] = $row['toko_id'];
    }
}

if(empty($toko_ids_in_cart)) {
    echo json_encode($response); // Kirim array kosong jika tidak ada toko yang relevan
    exit();
}

// 4. Query untuk mengambil semua voucher yang telah diklaim pengguna dan relevan dengan toko di keranjang
$toko_ids_placeholder = implode(',', array_fill(0, count($toko_ids_in_cart), '?'));

$query = "
    SELECT v.*
    FROM klaim_voucher kv
    JOIN voucher v ON kv.voucher_id = v.id
    WHERE kv.user_id = ? 
      AND v.toko_id IN ($toko_ids_placeholder)
      AND v.is_active = 1 
      AND v.tanggal_akhir > NOW()
      AND (v.jumlah_penggunaan_total IS NULL OR v.jumlah_digunakan_saat_ini < v.jumlah_penggunaan_total)
";

$stmt = $koneksi->prepare($query);

$types = 'i' . str_repeat('i', count($toko_ids_in_cart));
$params = array_merge([$user_id], $toko_ids_in_cart);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result_vouchers = $stmt->get_result();

$vouchers = [];
while ($voucher = $result_vouchers->fetch_assoc()) {
    // 5. Validasi kelayakan setiap voucher di sisi server
    $voucher['bisa_dipakai'] = true;
    $voucher['alasan_tidak_bisa'] = '';
    
    // Cek minimum pembelian
    if ($voucher['min_pembelian'] > 0 && $subtotal_produk < $voucher['min_pembelian']) {
        $voucher['bisa_dipakai'] = false;
        $butuh_belanja_lagi = number_format($voucher['min_pembelian'] - $subtotal_produk, 0, ',', '.');
        $voucher['alasan_tidak_bisa'] = "Butuh min. belanja Rp{$butuh_belanja_lagi} lagi.";
    }

    // Cek validitas voucher gratis ongkir
    if ($voucher['jenis_voucher'] == 'gratis_ongkir' && $biaya_ongkir <= 0) {
        $voucher['bisa_dipakai'] = false;
        $voucher['alasan_tidak_bisa'] = "Hanya berlaku untuk pengiriman.";
    }
    
    $vouchers[] = $voucher;
}
$stmt->close();

$response['vouchers'] = $vouchers;
echo json_encode($response);
$koneksi->close();
?>