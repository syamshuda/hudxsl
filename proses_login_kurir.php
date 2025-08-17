<?php
// /kurir/proses_login_kurir.php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username_kurir']);
$password = $_POST['password_kurir'];

if (empty($username) || empty($password)) {
    header("Location: login.php?error=Username dan password wajib diisi.");
    exit();
}

$stmt = $koneksi->prepare("SELECT * FROM kurir_lokal WHERE username_kurir = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $kurir = $result->fetch_assoc();
    if (password_verify($password, $kurir['password_kurir'])) {
        if ($kurir['is_active'] == 0) {
            header("Location: login.php?error=Akun Anda sedang tidak aktif.");
            exit();
        }

        // Login berhasil, buat sesi khusus untuk kurir
        session_regenerate_id(true);
        $_SESSION['kurir_id'] = $kurir['id'];
        $_SESSION['kurir_nama'] = $kurir['nama_kurir'];
        $_SESSION['toko_id_kurir'] = $kurir['toko_id'];
        
        header("Location: index.php");
        exit();
    }
}

// Jika username tidak ditemukan atau password salah
header("Location: login.php?error=Username atau password salah.");
exit();
?>