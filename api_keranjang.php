<?php
// /api_keranjang.php
require_once 'config/database.php';
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Aksi tidak valid.',
    'cart_count' => 0
];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Silakan login terlebih dahulu.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $produk_id = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

    if ($produk_id > 0 && $jumlah > 0) {
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }
        // Menambahkan atau mengupdate jumlah produk di keranjang
        $_SESSION['keranjang'][$produk_id] = $jumlah;
        
        $response['success'] = true;
        $response['message'] = 'Produk berhasil ditambahkan ke keranjang!';
    } else {
        $response['message'] = 'Data produk tidak valid.';
    }
}

// Selalu hitung dan kembalikan jumlah item di keranjang
$response['cart_count'] = isset($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0;

echo json_encode($response);
$koneksi->close();
?>