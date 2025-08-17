<?php
// /pesanan_diterima.php (Versi FINAL dengan penanganan error yang benar)
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['pesanan_id'])) {
    header("Location: /auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$pesanan_id = (int)$_POST['pesanan_id'];

mysqli_begin_transaction($koneksi);

try {
    // 1. Update status pesanan menjadi 'selesai'
    $stmt_update = $koneksi->prepare("UPDATE pesanan SET status_pesanan = 'selesai' WHERE id = ? AND pembeli_id = ? AND status_pesanan = 'dikirim'");
    $stmt_update->bind_param("ii", $pesanan_id, $user_id);
    if (!$stmt_update->execute() || $stmt_update->affected_rows == 0) {
        throw new Exception("Pesanan tidak ditemukan atau statusnya bukan 'dikirim'.");
    }
    $stmt_update->close();

    // 2. Panggil fungsi terpusat untuk proses finansial (termasuk cashback)
    selesaikanPesananDanTransferDana($pesanan_id, $koneksi);

    // 3. Jika semua berhasil, commit transaksi
    mysqli_commit($koneksi);
    header("Location: detail_pesanan.php?id=" . $pesanan_id);
    exit();

} catch (Exception $e) {
    // Jika terjadi error di mana pun, batalkan semua perubahan
    mysqli_rollback($koneksi);
    
    // Catat error untuk developer dan arahkan kembali dengan pesan yang jelas
    error_log("Error di pesanan_diterima.php: " . $e->getMessage());
    header("Location: detail_pesanan.php?id=" . $pesanan_id . "&error=" . urlencode("Terjadi kesalahan sistem saat memproses pesanan Anda. Silakan coba lagi nanti atau hubungi customer service."));
    exit();
} finally {
    if (isset($koneksi) && $koneksi instanceof mysqli) {
        $koneksi->close();
    }
}
?>