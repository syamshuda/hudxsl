<?php
// /tambah_wishlist.php
require_once 'config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: /auth/login.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$pembeli_id = $_SESSION['user_id'];
$produk_id = (int)$_GET['id'];

// Cek dulu agar tidak duplikat
$stmt_cek = $koneksi->prepare("SELECT id FROM wishlist WHERE pembeli_id = ? AND produk_id = ?");
$stmt_cek->bind_param("ii", $pembeli_id, $produk_id);
$stmt_cek->execute();
if ($stmt_cek->get_result()->num_rows > 0) {
    // Jika sudah ada, arahkan ke halaman wishlist
    header("Location: wishlist.php?status=sudah_ada");
    exit();
}

// Jika belum ada, tambahkan
$stmt_insert = $koneksi->prepare("INSERT INTO wishlist (pembeli_id, produk_id) VALUES (?, ?)");
$stmt_insert->bind_param("ii", $pembeli_id, $produk_id);
if ($stmt_insert->execute()) {
    header("Location: wishlist.php?status=sukses");
} else {
    // Gagal, kembali ke halaman produk
    header("Location: detail_produk.php?id=$produk_id&error=gagal");
}

$stmt_insert->close();
$koneksi->close();
?>