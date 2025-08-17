<?php
// /kurir/includes/header_kurir.php
require_once dirname(__DIR__, 2) . '/config/database.php';

// Proteksi halaman: pastikan kurir sudah login
// Pengecualian untuk halaman login itu sendiri
$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['kurir_id']) && $current_page !== 'login.php') {
    header("Location: " . BASE_URL . "/kurir/login.php");
    exit();
}

$nama_website = 'Portal Kurir Sabaku.ID';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . htmlspecialchars($nama_website) : htmlspecialchars($nama_website); ?></title>
    <link href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .navbar { background-color: #198754; } /* Warna hijau khas penjual */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/kurir/index.php">
            <i class="bi bi-box-seam-fill"></i>
            Portal Kurir
        </a>
        <?php if (isset($_SESSION['kurir_id'])): ?>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="proses_logout_kurir.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
        <?php endif; ?>
    </div>
</nav>

<main class="container">