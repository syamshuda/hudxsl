<?php
// /penjual/proses_update_pembayaran_kurir.php (Versi Final)
require_once '../config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Akses tidak sah.'];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$pesanan_id = isset($_POST['pesanan_id']) ? (int)$_POST['pesanan_id'] : 0;

if ($pesanan_id <= 0) {
    $response['message'] = 'ID Pesanan tidak valid.';
    echo json_encode($response);
    exit();
}

mysqli_begin_transaction($koneksi);

try {
    // Verifikasi kepemilikan pesanan untuk keamanan
    $stmt_verify = $koneksi->prepare("
        SELECT p.id FROM pesanan p
        JOIN detail_pesanan dp ON p.id = dp.pesanan_id
        JOIN produk pr ON dp.produk_id = pr.id
        JOIN toko t ON pr.toko_id = t.id
        WHERE p.id = ? AND t.user_id = ?
        LIMIT 1
    ");
    $stmt_verify->bind_param("ii", $pesanan_id, $user_id);
    $stmt_verify->execute();
    if ($stmt_verify->get_result()->num_rows === 0) {
        throw new Exception("Anda tidak memiliki akses ke pesanan ini.");
    }
    $stmt_verify->close();

    // Update status pembayaran kurir
    $stmt_update = $koneksi->prepare("UPDATE pesanan SET status_pembayaran_kurir = 'Sudah Dibayar' WHERE id = ? AND status_pembayaran_kurir = 'Belum Dibayar'");
    $stmt_update->bind_param("i", $pesanan_id);
    
    if (!$stmt_update->execute()) {
         throw new Exception('Gagal memperbarui status.');
    }
    
    if ($stmt_update->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Status pembayaran kurir berhasil diperbarui.';
    } else {
        // Ini terjadi jika statusnya memang sudah 'Sudah Dibayar'
        $response['success'] = true; // Anggap sukses karena tujuannya sudah tercapai
        $response['message'] = 'Status sudah "Sudah Dibayar".';
    }
    $stmt_update->close();
    
    mysqli_commit($koneksi);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    http_response_code(500);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$koneksi->close();
?>