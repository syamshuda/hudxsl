<?php
// /hapus_wishlist.php
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: /auth/login.php");
    exit();
}

$pembeli_id = $_SESSION['user_id'];
$produk_id = (int)$_GET['id'];

$stmt = $koneksi->prepare("DELETE FROM wishlist WHERE pembeli_id = ? AND produk_id = ?");
$stmt->bind_param("ii", $pembeli_id, $produk_id);
$stmt->execute();

header("Location: wishlist.php");
?>