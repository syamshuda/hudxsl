<?php
// /api_klaim_voucher.php
require_once 'config/database.php';
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Terjadi kesalahan tidak dikenal.'
];

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
        throw new Exception('Harap login sebagai pembeli untuk mengklaim voucher.');
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['voucher_id'])) {
        throw new Exception('Permintaan tidak valid.');
    }
    
    $user_id = $_SESSION['user_id'];
    $voucher_id = (int)$_POST['voucher_id'];

    // Cek apakah voucher valid dan masih tersedia
    $stmt_check = $koneksi->prepare("
        SELECT id FROM voucher 
        WHERE id = ? AND is_active = 1 AND tanggal_akhir > NOW() 
          AND (jumlah_penggunaan_total IS NULL OR jumlah_digunakan_saat_ini < jumlah_penggunaan_total)
    ");
    $stmt_check->bind_param("i", $voucher_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        throw new Exception('Voucher tidak valid atau sudah tidak tersedia.');
    }
    $stmt_check->close();

    // Coba masukkan ke tabel klaim
    $stmt_insert = $koneksi->prepare("INSERT INTO klaim_voucher (user_id, voucher_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $voucher_id);
    
    if ($stmt_insert->execute()) {
        $response['success'] = true;
        $response['message'] = 'Voucher berhasil diklaim!';
    } else {
        // Cek jika error karena duplikat (user sudah pernah klaim)
        if ($koneksi->errno == 1062) { // 1062 adalah kode error untuk duplikat entry
            throw new Exception('Anda sudah pernah mengklaim voucher ini.');
        } else {
            throw new Exception('Gagal mengklaim voucher: ' . $stmt_insert->error);
        }
    }
    $stmt_insert->close();

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$koneksi->close();
?>