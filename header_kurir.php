<?php
// /kurir/includes/header_kurir.php (Versi FINAL dengan Menu Lengkap)
require_once dirname(__DIR__, 2) . '/config/database.php';

$current_page = basename($_SERVER['PHP_SELF']);
if (!isset($_SESSION['kurir_id']) && $current_page !== 'login.php' && $current_page !== 'proses_login_kurir.php') {
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style> body { background-color: #f8f9fa; } .navbar { background-color: #198754; } </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>/kurir/index.php"><i class="bi bi-box-seam-fill"></i> Portal Kurir</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#courierNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <?php if (isset($_SESSION['kurir_id'])): ?>
        <div class="collapse navbar-collapse" id="courierNavbar">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Dasbor</a></li>
                <li class="nav-item"><a class="nav-link" href="pendapatan.php">Laporan Pendapatan</a></li>
                <li class="nav-item"><a class="nav-link" href="proses_logout_kurir.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</nav>
<main class="container">