<?php
// /auth/proses_login.php
require_once '../config/database.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Proteksi SQL Injection dengan Prepared Statements
$stmt = $koneksi->prepare("SELECT id, username, password, role, is_active FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Cek apakah akun aktif
    if ($user['is_active'] == 0) {
        header("Location: login.php?error=dinonaktifkan");
        exit();
    }

    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Login sukses, simpan data ke session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Arahkan berdasarkan role
        switch ($user['role']) {
            case 'admin':
                header("Location: ../admin/index.php");
                break;
            case 'penjual':
                header("Location: ../penjual/index.php");
                break;
            case 'pembeli':
                header("Location: ../index.php");
                break;
        }
        exit();
    }
}

// Jika login gagal
header("Location: login.php?error=salah");
exit();

$stmt->close();
$koneksi->close();
?>