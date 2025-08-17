<?php
// /api_ongkir.php - REVISI FINAL UNTUK MARKETPLACE
require_once 'config/database.php';
header('Content-Type: application/json');

$kurir = $_GET['kurir'] ?? '';
$kota_asal = strtoupper($_GET['kota_asal'] ?? '');
$kecamatan_asal = strtoupper($_GET['kecamatan_asal'] ?? '');
$kota_tujuan = strtoupper($_GET['kota_tujuan'] ?? '');
$kecamatan_tujuan = strtoupper($_GET['kecamatan_tujuan'] ?? '');
$total_berat = isset($_GET['berat']) ? (int)$_GET['berat'] : 0;

$response = ['success' => false, 'biaya' => 0, 'message' => 'Lengkapi data pengiriman.'];

if (empty($kurir) || empty($kota_asal) || empty($kota_tujuan)) {
    echo json_encode($response);
    exit();
}

if ($total_berat <= 0) {
    $total_berat = 1;
}

$biaya_dasar = 0;
$ditemukan = false;

// Logika pencarian yang lebih kompleks: coba cari berdasarkan kecamatan, jika tidak ada, coba berdasarkan kota
$stmt = $koneksi->prepare("SELECT biaya FROM ongkos_kirim WHERE kurir = ? AND kota_asal = ? AND kecamatan_asal = ? AND kota_tujuan = ? AND kecamatan_tujuan = ? LIMIT 1");
$stmt->bind_param("sssss", $kurir, $kota_asal, $kecamatan_asal, $kota_tujuan, $kecamatan_tujuan);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $biaya_dasar = (float) $result->fetch_assoc()['biaya'];
    $ditemukan = true;
}
$stmt->close();

if (!$ditemukan) {
    // Jika tidak ditemukan berdasarkan kecamatan, coba cari berdasarkan kota saja
    $stmt_kota = $koneksi->prepare("SELECT biaya FROM ongkos_kirim WHERE kurir = ? AND kota_asal = ? AND kota_tujuan = ? AND (kecamatan_asal IS NULL OR kecamatan_asal = '') AND (kecamatan_tujuan IS NULL OR kecamatan_tujuan = '') LIMIT 1");
    $stmt_kota->bind_param("sss", $kurir, $kota_asal, $kota_tujuan);
    $stmt_kota->execute();
    $result_kota = $stmt_kota->get_result();
    if ($result_kota->num_rows > 0) {
        $biaya_dasar = (float) $result_kota->fetch_assoc()['biaya'];
        $ditemukan = true;
    }
    $stmt_kota->close();
}

if ($ditemukan) {
    $kelipatan_berat = ceil($total_berat / 1000); // Ongkir per 1 kg
    $biaya_final = $biaya_dasar * $kelipatan_berat;

    $response['success'] = true;
    $response['biaya'] = $biaya_final;
    $response['message'] = 'Ongkir ditemukan.';
} else {
    $response['message'] = 'Tarif pengiriman untuk rute ini tidak tersedia.';
}

echo json_encode($response);
$koneksi->close();
?>