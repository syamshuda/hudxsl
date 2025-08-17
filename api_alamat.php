<?php
// /api_alamat.php (Versi Final dengan Logika Asal & Tujuan yang Benar)
require_once 'config/database.php';
header('Content-Type: application/json');

$get = $_GET['get'] ?? '';
$provinsi = $_GET['provinsi'] ?? '';
$kota = $_GET['kota'] ?? '';
$kecamatan_asal = $_GET['kecamatan_asal'] ?? ''; // Parameter kunci

$response = [];

try {
    // UNTUK FORM PENJUAL (Memilih Alamat ASAL)
    if ($get === 'asal_provinsi') {
        $result = $koneksi->query("SELECT DISTINCT provinsi_asal FROM ongkos_kirim ORDER BY provinsi_asal ASC");
        while ($row = $result->fetch_assoc()) { $response[] = $row['provinsi_asal']; }
    } 
    elseif ($get === 'asal_kota' && !empty($provinsi)) {
        $stmt = $koneksi->prepare("SELECT DISTINCT kota_asal FROM ongkos_kirim WHERE provinsi_asal = ? ORDER BY kota_asal ASC");
        $stmt->bind_param("s", $provinsi);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $response[] = $row['kota_asal']; }
        $stmt->close();
    } 
    elseif ($get === 'asal_kecamatan' && !empty($provinsi) && !empty($kota)) {
        $stmt = $koneksi->prepare("SELECT DISTINCT kecamatan_asal FROM ongkos_kirim WHERE provinsi_asal = ? AND kota_asal = ? ORDER BY kecamatan_asal ASC");
        $stmt->bind_param("ss", $provinsi, $kota);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $response[] = $row['kecamatan_asal']; }
        $stmt->close();
    }

    // UNTUK FORM PEMBELI DI CHECKOUT (Memilih Alamat TUJUAN)
    elseif ($get === 'tujuan_provinsi' && !empty($kecamatan_asal)) {
        $stmt = $koneksi->prepare("SELECT DISTINCT provinsi_tujuan FROM ongkos_kirim WHERE kecamatan_asal = ? ORDER BY provinsi_tujuan ASC");
        $stmt->bind_param("s", $kecamatan_asal);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $response[] = $row['provinsi_tujuan']; }
        $stmt->close();
    }
    elseif ($get === 'tujuan_kota' && !empty($kecamatan_asal) && !empty($provinsi)) {
        $stmt = $koneksi->prepare("SELECT DISTINCT kota_tujuan FROM ongkos_kirim WHERE kecamatan_asal = ? AND provinsi_tujuan = ? ORDER BY kota_tujuan ASC");
        $stmt->bind_param("ss", $kecamatan_asal, $provinsi);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $response[] = $row['kota_tujuan']; }
        $stmt->close();
    } 
    elseif ($get === 'tujuan_kecamatan' && !empty($kecamatan_asal) && !empty($provinsi) && !empty($kota)) {
        $stmt = $koneksi->prepare("SELECT DISTINCT kecamatan_tujuan FROM ongkos_kirim WHERE kecamatan_asal = ? AND provinsi_tujuan = ? AND kota_tujuan = ? ORDER BY kecamatan_tujuan ASC");
        $stmt->bind_param("sss", $kecamatan_asal, $provinsi, $kota);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) { $response[] = $row['kecamatan_tujuan']; }
        $stmt->close();
    }

} catch (Exception $e) { 
    http_response_code(500);
    $response = ['error' => 'Terjadi kesalahan pada server.'];
}

echo json_encode($response);
$koneksi->close();
?>