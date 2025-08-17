<?php
// /kurir/proses_lapor.php (Versi Final)
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['kurir_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$kurir_id = $_SESSION['kurir_id'];
$pesanan_id = isset($_POST['pesanan_id']) ? (int)$_POST['pesanan_id'] : 0;
$status_laporan = $_POST['status_pengantaran']; // 'selesai' atau 'gagal'
$kurir_catatan = trim($_POST['kurir_catatan'] ?? '');
$kurir_jumlah_cod = isset($_POST['kurir_jumlah_cod']) ? (float)$_POST['kurir_jumlah_cod'] : NULL;

mysqli_begin_transaction($koneksi);

try {
    // 1. Verifikasi bahwa kurir ini memang ditugaskan untuk pesanan ini dan statusnya 'dikirim'
    $stmt_verify = $koneksi->prepare("SELECT id FROM pesanan WHERE id = ? AND kurir_lokal_id = ? AND status_pesanan = 'dikirim'");
    $stmt_verify->bind_param("ii", $pesanan_id, $kurir_id);
    $stmt_verify->execute();
    if ($stmt_verify->get_result()->num_rows !== 1) {
        throw new Exception("Tugas tidak valid atau sudah dilaporkan sebelumnya.");
    }
    $stmt_verify->close();

    // 2. Proses upload foto bukti
    $nama_foto_bukti = null;
    if (isset($_FILES['kurir_foto_bukti']) && $_FILES['kurir_foto_bukti']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/bukti_pengantaran/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                throw new Exception("Gagal membuat direktori upload.");
            }
        }
        $nama_foto_bukti = "bukti_" . $pesanan_id . "_" . uniqid() . "." . pathinfo($_FILES['kurir_foto_bukti']['name'], PATHINFO_EXTENSION);
        $target_file = $target_dir . $nama_foto_bukti;

        if (!move_uploaded_file($_FILES['kurir_foto_bukti']["tmp_name"], $target_file)) {
            throw new Exception("Gagal mengunggah foto bukti.");
        }
    } else {
        throw new Exception("Foto bukti wajib diunggah.");
    }

    // 3. Tentukan status pesanan baru berdasarkan laporan
    // Anda bisa menambahkan logika status 'pengiriman_gagal' di database jika diperlukan
    $status_pesanan_baru = ($status_laporan == 'selesai') ? 'selesai' : 'dikirim'; 
    
    $stmt_update = $koneksi->prepare("UPDATE pesanan SET status_pesanan = ?, kurir_foto_bukti = ?, kurir_catatan = ?, kurir_jumlah_cod = ? WHERE id = ?");
    $stmt_update->bind_param("sssdi", $status_pesanan_baru, $nama_foto_bukti, $kurir_catatan, $kurir_jumlah_cod, $pesanan_id);
    
    if (!$stmt_update->execute()) {
        throw new Exception("Gagal menyimpan laporan ke database.");
    }
    $stmt_update->close();
    
    // 4. Jika pengiriman berhasil, panggil fungsi penyelesaian keuangan
    if ($status_pesanan_baru == 'selesai') {
        selesaikanPesananDanTransferDana($pesanan_id, $koneksi);
    }
    
    mysqli_commit($koneksi);
    header("Location: index.php?status=sukses_lapor&id=" . $pesanan_id);
    exit();

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    header("Location: detail_tugas.php?id=" . $pesanan_id . "&error=" . urlencode($e->getMessage()));
    exit();
}
?>