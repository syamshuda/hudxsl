<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$produk_id = (int)$_POST['produk_id'];
$pesanan_id = (int)$_POST['pesanan_id'];
$pembeli_id = $_SESSION['user_id'];
$rating = (int)$_POST['rating'];
$komentar = trim($_POST['komentar']);

// Validasi
if ($rating < 1 || $rating > 5) {
    die("Rating tidak valid.");
}

// Cek apakah user berhak memberi ulasan
$stmt_verify = $koneksi->prepare("
    SELECT p.id FROM pesanan p 
    JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    WHERE p.id = ? AND p.pembeli_id = ? AND dp.produk_id = ? AND p.status_pesanan = 'selesai'
");
$stmt_verify->bind_param("iii", $pesanan_id, $pembeli_id, $produk_id);
$stmt_verify->execute();
if ($stmt_verify->get_result()->num_rows == 0) {
    die("Anda tidak berhak memberikan ulasan untuk produk ini.");
}

// Cek agar tidak memberi ulasan dobel
$stmt_cek = $koneksi->prepare("SELECT id FROM ulasan WHERE pembeli_id = ? AND produk_id = ? AND pesanan_id = ?");
$stmt_cek->bind_param("iii", $pembeli_id, $produk_id, $pesanan_id);
$stmt_cek->execute();
if ($stmt_cek->get_result()->num_rows > 0) {
    die("Anda sudah pernah memberi ulasan untuk produk ini dari pesanan ini.");
}
$stmt_cek->close();

// Insert ulasan
$stmt_insert = $koneksi->prepare("INSERT INTO ulasan (produk_id, pembeli_id, pesanan_id, rating, komentar) VALUES (?, ?, ?, ?, ?)");
$stmt_insert->bind_param("iiiis", $produk_id, $pembeli_id, $pesanan_id, $rating, $komentar);

if ($stmt_insert->execute()) {
    header("Location: detail_produk.php?id=$produk_id&ulasan=sukses");
} else {
    echo "Gagal menyimpan ulasan.";
}
$stmt_insert->close();
$koneksi->close();
?>