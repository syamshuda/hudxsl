<?php
// /penjual/proses_update_pesanan.php (Versi FINAL dengan penyimpanan gaji kurir)
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$pesanan_id = (int)$_POST['pesanan_id'];
$action = $_POST['action'];
$user_id = $_SESSION['user_id'];

if ($action !== 'kirim') {
    header("Location: pesanan_masuk.php");
    exit();
}

mysqli_begin_transaction($koneksi);

try {
    // Verifikasi kepemilikan pesanan
    $stmt_verify = $koneksi->prepare("SELECT dp.id FROM detail_pesanan dp JOIN produk pr ON dp.produk_id = pr.id JOIN toko t ON pr.toko_id = t.id WHERE dp.pesanan_id = ? AND t.user_id = ? LIMIT 1");
    $stmt_verify->bind_param("ii", $pesanan_id, $user_id);
    $stmt_verify->execute();
    if ($stmt_verify->get_result()->num_rows == 0) {
        throw new Exception("Anda tidak memiliki akses ke pesanan ini.");
    }
    $stmt_verify->close();
    
    $nomor_resi = trim($_POST['nomor_resi']);
    $kurir_lokal_id = isset($_POST['kurir_lokal_id']) ? (int)$_POST['kurir_lokal_id'] : NULL;
    // ================== LOGIKA BARU: Ambil Gaji Kurir ==================
    $gaji_kurir = isset($_POST['gaji_kurir']) ? (float)$_POST['gaji_kurir'] : 0.00;
    // ====================================================================
    
    // Menyiapkan query UPDATE
    $stmt_update = $koneksi->prepare(
        "UPDATE pesanan 
         SET status_pesanan = 'dikirim', 
             nomor_resi = ?, 
             kurir_lokal_id = ?, 
             gaji_kurir = ?, 
             tanggal_dikirim = NOW() 
         WHERE id = ?"
    );
    // Bind 4 parameter sekarang
    $stmt_update->bind_param("sidi", $nomor_resi, $kurir_lokal_id, $gaji_kurir, $pesanan_id);

    if (!$stmt_update->execute()) {
        throw new Exception("Gagal update status pesanan menjadi 'dikirim'.");
    }
    $stmt_update->close();
    
    // Kirim notifikasi ke pembeli
    $pembeli_id_res = mysqli_query($koneksi, "SELECT pembeli_id FROM pesanan WHERE id = $pesanan_id");
    $pembeli_id = mysqli_fetch_assoc($pembeli_id_res)['pembeli_id'];
    
    $judul_notif = "Pesanan Dikirim!";
    $pesan_notif = "Pesanan Anda #$pesanan_id telah dikirim. " . (!empty($nomor_resi) ? "Keterangan: $nomor_resi" : "");
    $link_notif = "/detail_pesanan.php?id=$pesanan_id";
    
    $stmt_ins_notif = $koneksi->prepare("INSERT INTO notifikasi (user_id, judul, pesan, link, tipe) VALUES (?, ?, ?, ?, 'pembeli')");
    $stmt_ins_notif->bind_param("isss", $pembeli_id, $judul_notif, $pesan_notif, $link_notif);
    $stmt_ins_notif->execute();
    $stmt_ins_notif->close();
    
    mysqli_commit($koneksi);
    header("Location: pesanan_masuk.php?status=sukses_kirim");

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    header("Location: detail_pesanan.php?id=$pesanan_id&error=" . urlencode($e->getMessage()));
} finally {
    if (isset($koneksi)) $koneksi->close();
    exit();
}
?>