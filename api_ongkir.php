<?php
// /api_ongkir.php (Versi Final untuk Kalkulasi Berbasis Asal & Tujuan)
require_once 'config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'biaya' => 0, 'message' => 'Parameter tidak lengkap.'];

try {
    $kurir = $_GET['kurir'] ?? '';
    $kecamatan_asal = $_GET['kecamatan_asal'] ?? '';
    $kecamatan_tujuan = $_GET['kecamatan_tujuan'] ?? '';
    $total_berat = isset($_GET['berat']) ? (int)$_GET['berat'] : 0;

    if (empty($kurir) || empty($kecamatan_asal) || empty($kecamatan_tujuan)) {
        throw new Exception('Kurir, kecamatan asal, dan kecamatan tujuan wajib diisi.');
    }

    if ($total_berat <= 0) {
        $total_berat = 1; // Berat minimal untuk kalkulasi
    }

    $stmt = $koneksi->prepare(
        "SELECT biaya FROM ongkos_kirim WHERE kurir = ? AND kecamatan_asal = ? AND kecamatan_tujuan = ? LIMIT 1"
    );
    $stmt->bind_param("sss", $kurir, $kecamatan_asal, $kecamatan_tujuan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $biaya_dasar = (float) $result->fetch_assoc()['biaya'];
        // Hitung kelipatan berat (1000 gram = 1 kg), bulatkan ke atas
        $kelipatan_berat = ceil($total_berat / 1000);
        $biaya_final = $biaya_dasar * $kelipatan_berat;

        $response['success'] = true;
        $response['biaya'] = $biaya_final;
        $response['message'] = 'Ongkir berhasil dihitung.';
    } else {
        $response['message'] = 'Tarif pengiriman untuk rute ini tidak tersedia.';
    }
    $stmt->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$koneksi->close();
?>