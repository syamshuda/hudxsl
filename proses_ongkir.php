<?php
require_once '../config/database.php';

// Proteksi: Pastikan user adalah admin dan request adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'tambah' || $action === 'edit') {
        // Ambil semua data dari form dan ubah ke huruf besar untuk konsistensi
        $kurir = trim($_POST['kurir']);
        $provinsi_asal = trim(strtoupper($_POST['provinsi_asal']));
        $kota_asal = trim(strtoupper($_POST['kota_asal']));
        $kecamatan_asal = trim(strtoupper($_POST['kecamatan_asal']));
        $provinsi_tujuan = trim(strtoupper($_POST['provinsi_tujuan']));
        $kota_tujuan = trim(strtoupper($_POST['kota_tujuan']));
        $kecamatan_tujuan = trim(strtoupper($_POST['kecamatan_tujuan']));
        $biaya = (float)$_POST['biaya'];
        $estimasi = trim($_POST['estimasi'] ?? '');

        // Validasi data
        if (empty($kurir) || empty($provinsi_asal) || empty($kota_asal) || empty($kecamatan_asal) || empty($provinsi_tujuan) || empty($kota_tujuan) || empty($kecamatan_tujuan) || $biaya < 0) {
            throw new Exception("Semua data wajib diisi dengan benar.");
        }

        if ($action === 'tambah') {
            $stmt = $koneksi->prepare("INSERT INTO ongkos_kirim (kurir, provinsi_asal, kota_asal, kecamatan_asal, provinsi_tujuan, kota_tujuan, kecamatan_tujuan, biaya, estimasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            // Tipe data yang benar: sssssssds
            $stmt->bind_param("sssssssds", $kurir, $provinsi_asal, $kota_asal, $kecamatan_asal, $provinsi_tujuan, $kota_tujuan, $kecamatan_tujuan, $biaya, $estimasi);
            $pesan_sukses = "Tarif berhasil ditambahkan.";
        } else { // Logika untuk EDIT
            $id = (int)$_POST['id'];
            if ($id <= 0) throw new Exception("ID tarif tidak valid.");
            $stmt = $koneksi->prepare("UPDATE ongkos_kirim SET kurir=?, provinsi_asal=?, kota_asal=?, kecamatan_asal=?, provinsi_tujuan=?, kota_tujuan=?, kecamatan_tujuan=?, biaya=?, estimasi=? WHERE id=?");
            // Tipe data yang benar: sssssssdsi
            $stmt->bind_param("sssssssdsi", $kurir, $provinsi_asal, $kota_asal, $kecamatan_asal, $provinsi_tujuan, $kota_tujuan, $kecamatan_tujuan, $biaya, $estimasi, $id);
            $pesan_sukses = "Tarif berhasil diperbarui.";
        }

        if (!$stmt->execute()) throw new Exception("Gagal menyimpan data ke database: " . $stmt->error);
        $stmt->close();
        header("Location: kelola_ongkir.php?status=sukses&pesan=" . urlencode($pesan_sukses));
        exit();

    } elseif ($action === 'hapus') {
        $id = (int)$_POST['id'];
        if ($id <= 0) throw new Exception("ID tarif tidak valid.");
        $stmt = $koneksi->prepare("DELETE FROM ongkos_kirim WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) throw new Exception("Gagal menghapus data: " . $stmt->error);
        $stmt->close();
        header("Location: kelola_ongkir.php?status=sukses&pesan=Tarif berhasil dihapus.");
        exit();
    } else {
        throw new Exception("Aksi tidak valid.");
    }
} catch (Exception $e) {
    header("Location: kelola_ongkir.php?status=gagal&pesan=" . urlencode($e->getMessage()));
    exit();
} finally {
    if (isset($koneksi)) {
        $koneksi->close();
    }
}
?>