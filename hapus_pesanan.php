<?php
// /hapus_pesanan.php
require_once 'config/database.php';

// Proteksi: pastikan user login dan mengirim data yang benar
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pesanan_id'])) {
    header("Location: /auth/login.php");
        exit();
        }

        $user_id = $_SESSION['user_id'];
        $pesanan_id = (int)$_POST['pesanan_id'];

        // Siapkan query untuk menghapus pesanan
        // Pastikan hanya pesanan milik user yang login dan statusnya 'menunggu_pembayaran' yang bisa dihapus
        $stmt = $koneksi->prepare("DELETE FROM pesanan WHERE id = ? AND pembeli_id = ? AND status_pesanan = 'menunggu_pembayaran'");
        $stmt->bind_param("ii", $pesanan_id, $user_id);
        $stmt->execute();
        $stmt->close();
        $koneksi->close();

        // Arahkan kembali ke halaman riwayat pesanan
        header("Location: /pesanan_saya.php");
        exit();
        ?>