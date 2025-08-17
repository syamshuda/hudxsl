<?php
// /penjual/proses_voucher.php (Versi Final dengan Semua Aksi)
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

$stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
$stmt_toko->bind_param("i", $user_id);
$stmt_toko->execute();
$toko_id = $stmt_toko->get_result()->fetch_assoc()['id'];
$stmt_toko->close();

if (!$toko_id) { header("Location: kelola_voucher.php?status=gagal&pesan=Toko tidak ditemukan."); exit(); }

try {
    switch ($action) {
        case 'tambah':
        case 'edit':
            $kode = trim($_POST['kode']);
            $jenis_voucher = $_POST['jenis_voucher'];
            $nilai = (float)$_POST['nilai'];
            $min_pembelian = !empty($_POST['min_pembelian']) ? (float)$_POST['min_pembelian'] : NULL;
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $tanggal_akhir = $_POST['tanggal_akhir'];
            $jumlah_penggunaan_total = !empty($_POST['jumlah_penggunaan_total']) ? (int)$_POST['jumlah_penggunaan_total'] : NULL;
            $limit_per_pembeli = !empty($_POST['limit_per_pembeli']) ? (int)$_POST['limit_per_pembeli'] : 1;

            // ... (validasi data) ...

            if ($action === 'tambah') {
                $stmt = $koneksi->prepare("INSERT INTO voucher (toko_id, kode, jenis_voucher, nilai, min_pembelian, tanggal_mulai, tanggal_akhir, jumlah_penggunaan_total, limit_per_pembeli) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issddssii", $toko_id, $kode, $jenis_voucher, $nilai, $min_pembelian, $tanggal_mulai, $tanggal_akhir, $jumlah_penggunaan_total, $limit_per_pembeli);
            } else { // edit
                $voucher_id = (int)$_POST['voucher_id'];
                $stmt = $koneksi->prepare("UPDATE voucher SET kode=?, jenis_voucher=?, nilai=?, min_pembelian=?, tanggal_mulai=?, tanggal_akhir=?, jumlah_penggunaan_total=?, limit_per_pembeli=? WHERE id=? AND toko_id=?");
                $stmt->bind_param("ssddssiiii", $kode, $jenis_voucher, $nilai, $min_pembelian, $tanggal_mulai, $tanggal_akhir, $jumlah_penggunaan_total, $limit_per_pembeli, $voucher_id, $toko_id);
            }
            if (!$stmt->execute()) throw new Exception("Gagal menyimpan voucher.");
            break;

        case 'toggle_status':
            $voucher_id = (int)$_POST['voucher_id'];
            $current_status = (int)$_POST['current_status'];
            $new_status = $current_status == 1 ? 0 : 1;
            $stmt = $koneksi->prepare("UPDATE voucher SET is_active = ? WHERE id = ? AND toko_id = ?");
            $stmt->bind_param("iii", $new_status, $voucher_id, $toko_id);
            if (!$stmt->execute()) throw new Exception("Gagal mengubah status voucher.");
            break;

        default:
            throw new Exception("Aksi tidak valid.");
    }
    
    $stmt->close();
    header("Location: kelola_voucher.php?status=sukses");
    exit();

} catch (Exception $e) {
    header("Location: kelola_voucher.php?status=gagal&pesan=" . urlencode($e->getMessage()));
    exit();
}
?>