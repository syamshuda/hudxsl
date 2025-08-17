<?php
// /admin/proses_moderasi_produk.php
require_once '../config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$produk_id = (int)$_POST['produk_id'];
$action = $_POST['action']; // 'setujui' atau 'tolak'

if ($action === 'setujui') {
    $new_status = 'disetujui';
} elseif ($action === 'tolak') {
    $new_status = 'ditolak';
} else {
    header("Location: kelola_produk.php");
    exit();
}

$stmt = $koneksi->prepare("UPDATE produk SET status_moderasi = ? WHERE id = ?");
$stmt->bind_param("si", $new_status, $produk_id);

if ($stmt->execute()) {
    header("Location: kelola_produk.php?status=sukses");
} else {
    header("Location: kelola_produk.php?status=gagal");
}

$stmt->close();
$koneksi->close();
?>