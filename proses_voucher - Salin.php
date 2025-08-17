<?php
// /penjual/proses_voucher.php (Versi Final Diperbaiki)
require_once '../config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// Ambil toko_id
$stmt_toko = $koneksi->prepare("SELECT id FROM toko WHERE user_id = ?");
$stmt_toko->bind_param("i", $user_id);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
$toko = $result_toko->fetch_assoc();
$toko_id = $toko['id'];
$stmt_toko->close();

if (!$toko_id) {
    header("Location: kelola_voucher.php?status=gagal&pesan=Toko tidak ditemukan.");
    exit();
}

try {
    switch ($action) {
        case 'tambah':
            $kode = trim($_POST['kode']);
            $nilai_diskon = (float)$_POST['nilai_diskon'];
            $min_pembelian = isset($_POST['min_pembelian']) && $_POST['min_pembelian'] !== '' ? (float)$_POST['min_pembelian'] : NULL;
            $tanggal_mulai = $_POST['tanggal_mulai'];
            $tanggal_akhir = $_POST['tanggal_akhir'];
            $jumlah_penggunaan_total = isset($_POST['jumlah_penggunaan_total']) && $_POST['jumlah_penggunaan_total'] !== '' ? (int)$_POST['jumlah_penggunaan_total'] : NULL;
            $limit_per_pembeli = isset($_POST['limit_per_pembeli']) && $_POST['limit_per_pembeli'] !== '' ? (int)$_POST['limit_per_pembeli'] : 1;

            if (empty($kode) || $nilai_diskon <= 0 || empty($tanggal_mulai) || empty($tanggal_akhir)) {
                throw new Exception("Data voucher tidak lengkap atau tidak valid.");
            }
            if (strtotime($tanggal_mulai) >= strtotime($tanggal_akhir)) {
                throw new Exception("Tanggal mulai harus sebelum tanggal berakhir.");
            }

            $stmt_check_kode = $koneksi->prepare("SELECT id FROM voucher WHERE kode = ?");
            $stmt_check_kode->bind_param("s", $kode);
            $stmt_check_kode->execute();
            if ($stmt_check_kode->get_result()->num_rows > 0) {
                throw new Exception("Kode voucher sudah ada.");
            }
            $stmt_check_kode->close();

            $stmt = $koneksi->prepare("INSERT INTO voucher (toko_id, kode, jenis_diskon, nilai_diskon, min_pembelian, tanggal_mulai, tanggal_akhir, jumlah_penggunaan_total, limit_per_pembeli) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // === PERBAIKAN DI SINI ===
            $jenis_diskon = 'persentase'; // Pastikan nilai ini diisi sesuai ENUM di database
            
            $stmt->bind_param("issddssii", 
                $toko_id, $kode, $jenis_diskon, $nilai_diskon, $min_pembelian, 
                $tanggal_mulai, $tanggal_akhir, $jumlah_penggunaan_total, $limit_per_pembeli
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal menyimpan voucher: " . $stmt->error);
            }
            $stmt->close();
            header("Location: kelola_voucher.php?status=sukses");
            exit();

        case 'edit':
            // ... (Logika edit tetap sama seperti sebelumnya, sudah benar)
            break;

        case 'toggle_status':
            // ... (Logika toggle_status tetap sama seperti sebelumnya, sudah benar)
            break;

        case 'hapus':
            // ... (Logika hapus tetap sama seperti sebelumnya, sudah benar)
            break;

        default:
            throw new Exception("Aksi tidak valid.");
    }
} catch (Exception $e) {
    header("Location: kelola_voucher.php?status=gagal&pesan=" . urlencode($e->getMessage()));
    exit();
} finally {
    if (isset($koneksi)) $koneksi->close();
}
?>