<?php
// /penjual/proses_kurir.php (Versi FINAL dengan Aksi Edit Gaji)
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

if (!$toko_id) {
    header("Location: kelola_kurir.php?status=gagal&pesan=Profil toko tidak ditemukan.");
    exit();
}

try {
    switch ($action) {
        case 'tambah':
            $nama_kurir = trim($_POST['nama_kurir']);
            $no_telepon = trim($_POST['no_telepon']);
            $username_kurir = trim($_POST['username_kurir']);
            $password_kurir = $_POST['password_kurir'];

            if (empty($nama_kurir) || empty($no_telepon) || empty($username_kurir) || empty($password_kurir)) {
                throw new Exception("Semua kolom wajib diisi.");
            }
            if (strlen($password_kurir) < 6) {
                throw new Exception("Password minimal harus 6 karakter.");
            }

            $stmt_check = $koneksi->prepare("SELECT id FROM kurir_lokal WHERE username_kurir = ?");
            $stmt_check->bind_param("s", $username_kurir);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                throw new Exception("Username sudah digunakan. Harap gunakan username lain.");
            }
            $stmt_check->close();

            $hashed_password = password_hash($password_kurir, PASSWORD_DEFAULT);

            $stmt = $koneksi->prepare("INSERT INTO kurir_lokal (toko_id, nama_kurir, no_telepon, username_kurir, password_kurir) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $toko_id, $nama_kurir, $no_telepon, $username_kurir, $hashed_password);
            $pesan_sukses = "Akun kurir berhasil dibuat.";
            break;

        case 'edit':
            $kurir_id = (int)$_POST['kurir_id'];
            $nama_kurir = trim($_POST['nama_kurir']);
            $no_telepon = trim($_POST['no_telepon']);
            $username_kurir = trim($_POST['username_kurir']);
            $password_kurir = $_POST['password_kurir'];
            $tipe_gaji = $_POST['tipe_gaji'];
            $nilai_gaji = (float)$_POST['nilai_gaji'];

            if (empty($nama_kurir) || empty($no_telepon) || empty($username_kurir)) {
                throw new Exception("Nama, nomor telepon, dan username wajib diisi.");
            }

            $stmt_check = $koneksi->prepare("SELECT id FROM kurir_lokal WHERE username_kurir = ? AND id != ?");
            $stmt_check->bind_param("si", $username_kurir, $kurir_id);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows > 0) {
                throw new Exception("Username sudah digunakan oleh kurir lain.");
            }
            $stmt_check->close();

            if (!empty($password_kurir)) {
                if (strlen($password_kurir) < 6) {
                    throw new Exception("Password minimal harus 6 karakter.");
                }
                $hashed_password = password_hash($password_kurir, PASSWORD_DEFAULT);
                $stmt = $koneksi->prepare("UPDATE kurir_lokal SET nama_kurir=?, no_telepon=?, username_kurir=?, password_kurir=?, tipe_gaji=?, nilai_gaji=? WHERE id=? AND toko_id=?");
                $stmt->bind_param("sssssdii", $nama_kurir, $no_telepon, $username_kurir, $hashed_password, $tipe_gaji, $nilai_gaji, $kurir_id, $toko_id);
            } else {
                $stmt = $koneksi->prepare("UPDATE kurir_lokal SET nama_kurir=?, no_telepon=?, username_kurir=?, tipe_gaji=?, nilai_gaji=? WHERE id=? AND toko_id=?");
                $stmt->bind_param("ssssdii", $nama_kurir, $no_telepon, $username_kurir, $tipe_gaji, $nilai_gaji, $kurir_id, $toko_id);
            }
            $pesan_sukses = "Informasi kurir berhasil diperbarui.";
            break;
        
        case 'aktifkan':
        case 'nonaktifkan':
            $kurir_id = (int)$_POST['kurir_id'];
            $new_status = ($action === 'aktifkan') ? 1 : 0;
            $stmt = $koneksi->prepare("UPDATE kurir_lokal SET is_active = ? WHERE id = ? AND toko_id = ?");
            $stmt->bind_param("iii", $new_status, $kurir_id, $toko_id);
            $pesan_sukses = "Status kurir berhasil diperbarui.";
            break;

        case 'hapus':
            $kurir_id = (int)$_POST['kurir_id'];
            $stmt = $koneksi->prepare("DELETE FROM kurir_lokal WHERE id = ? AND toko_id = ?");
            $stmt->bind_param("ii", $kurir_id, $toko_id);
            $pesan_sukses = "Kurir berhasil dihapus.";
            break;

        default:
            throw new Exception("Aksi tidak valid.");
    }

    if (!$stmt->execute()) {
        throw new Exception("Operasi database gagal: " . $stmt->error);
    }
    
    $stmt->close();
    header("Location: kelola_kurir.php?status=sukses&pesan=" . urlencode($pesan_sukses));
    exit();

} catch (Exception $e) {
    $redirect_url = ($action === 'edit' && isset($_POST['kurir_id'])) ? "edit_kurir.php?id=" . (int)$_POST['kurir_id'] : "kelola_kurir.php";
    header("Location: $redirect_url?status=gagal&pesan=" . urlencode($e->getMessage()));
    exit();
}
?>