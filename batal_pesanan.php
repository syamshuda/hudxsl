<?php
// /batal_pesanan.php
require_once 'config/database.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['pesanan_id'])) {
    header("Location: /auth/login.php");
        exit();
        }

        $user_id = $_SESSION['user_id'];
        $pesanan_id = (int)$_POST['pesanan_id'];

        // Anda bisa memilih antara menghapus pesanan atau mengubah statusnya.
        // Mengubah status lebih disarankan untuk arsip data.

        // Opsi 1: Ubah status menjadi 'dibatalkan'
        $stmt = $koneksi->prepare("UPDATE pesanan SET status_pesanan = 'dibatalkan' WHERE id = ? AND pembeli_id = ? AND status_pesanan = 'menunggu_pembayaran'");
        $stmt->bind_param("ii", $pesanan_id, $user_id);
        $stmt->execute();

        /*
        // Opsi 2: Hapus pesanan dari database (jika Anda lebih suka ini)
        $stmt = $koneksi->prepare("DELETE FROM pesanan WHERE id = ? AND pembeli_id = ? AND status_pesanan = 'menunggu_pembayaran'");
        $stmt->bind_param("ii", $pesanan_id, $user_id);
        $stmt->execute();
        */

        $stmt->close();
        $koneksi->close();

        // Arahkan kembali ke halaman riwayat pesanan
        header("Location: /pesanan_saya.php?status=dibatalkan");
        exit();
        ?>