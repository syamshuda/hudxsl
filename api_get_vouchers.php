<?php
// /api_get_vouchers.php (Versi Final dengan Pengecekan Batas Pembeli)
require_once 'config/database.php';
header('Content-Type: application/json');

$response = ['vouchers' => []];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    http_response_code(401);
    echo json_encode(['message' => 'Harap login sebagai pembeli.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$subtotal_produk = isset($_GET['subtotal']) ? (float)$_GET['subtotal'] : 0;
$biaya_ongkir = isset($_GET['ongkir']) ? (float)$_GET['ongkir'] : 0;
$toko_ids_in_cart = [];

$items_in_cart = $_SESSION['keranjang'] ?? ($_SESSION['buy_now_item'] ? [$_SESSION['buy_now_item']['produk_id'] => $_SESSION['buy_now_item']['jumlah']] : []);

if (empty($items_in_cart)) {
    echo json_encode($response);
    exit();
}

$ids_string = implode(',', array_map('intval', array_keys($items_in_cart)));
$result_toko = mysqli_query($koneksi, "SELECT DISTINCT toko_id FROM produk WHERE id IN ($ids_string)");
if ($result_toko) {
    while ($row = mysqli_fetch_assoc($result_toko)) {
        $toko_ids_in_cart[] = $row['toko_id'];
    }
}

if(empty($toko_ids_in_cart)) {
    echo json_encode($response);
    exit();
}

$toko_ids_placeholder = implode(',', array_fill(0, count($toko_ids_in_cart), '?'));

// ======== QUERY BARU: Mengambil jumlah penggunaan per pembeli ========
$query = "
    SELECT v.*, pv.jumlah_digunakan
    FROM klaim_voucher kv
    JOIN voucher v ON kv.voucher_id = v.id
    LEFT JOIN penggunaan_voucher pv ON v.id = pv.voucher_id AND pv.pembeli_id = ?
    WHERE kv.user_id = ? 
      AND v.toko_id IN ($toko_ids_placeholder)
      AND v.is_active = 1 
      AND v.tanggal_akhir > NOW()
      AND (v.jumlah_penggunaan_total IS NULL OR v.jumlah_digunakan_saat_ini < v.jumlah_penggunaan_total)
";
// ==================================================================

$stmt = $koneksi->prepare($query);

$types = 'i' . 'i' . str_repeat('i', count($toko_ids_in_cart)); // Tambah satu 'i' untuk user_id di LEFT JOIN
$params = array_merge([$user_id, $user_id], $toko_ids_in_cart);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result_vouchers = $stmt->get_result();

$vouchers = [];
while ($voucher = $result_vouchers->fetch_assoc()) {
    $voucher['bisa_dipakai'] = true;
    $voucher['alasan_tidak_bisa'] = '';
    
    // Cek minimum pembelian
    if ($voucher['min_pembelian'] > 0 && $subtotal_produk < $voucher['min_pembelian']) {
        $voucher['bisa_dipakai'] = false;
        $butuh_belanja_lagi = number_format($voucher['min_pembelian'] - $subtotal_produk, 0, ',', '.');
        $voucher['alasan_tidak_bisa'] = "Butuh min. belanja Rp{$butuh_belanja_lagi} lagi.";
    }

    // ========== LOGIKA BARU: Pengecekan Batas Penggunaan Per Pembeli ==========
    $jumlah_telah_digunakan = (int)($voucher['jumlah_digunakan'] ?? 0);
    if ($jumlah_telah_digunakan >= $voucher['limit_per_pembeli']) {
        $voucher['bisa_dipakai'] = false;
        $voucher['alasan_tidak_bisa'] = "Anda telah mencapai batas penggunaan voucher ini.";
    }
    // ========================================================================

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