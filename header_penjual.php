<?php
// /includes/header_penjual.php

// Pastikan path ke file database benar
require_once __DIR__ . '/../config/database.php';

// Proteksi halaman: Pastikan pengguna adalah 'penjual' yang sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Logika untuk memeriksa kelengkapan alamat
$stmt_check_toko = $koneksi->prepare("SELECT alamat_lengkap, provinsi, kota, kecamatan, kode_pos FROM toko WHERE user_id = ? LIMIT 1");
$stmt_check_toko->bind_param("i", $user_id);
$stmt_check_toko->execute();
$toko_check_result = $stmt_check_toko->get_result()->fetch_assoc();
$stmt_check_toko->close();

$is_alamat_lengkap = $toko_check_result && !empty($toko_check_result['alamat_lengkap']) && !empty($toko_check_result['provinsi']) && !empty($toko_check_result['kota']) && !empty($toko_check_result['kecamatan']) && !empty($toko_check_result['kode_pos']);

$current_script = basename($_SERVER['PHP_SELF']);

// Redirect jika alamat belum lengkap
if (!$is_alamat_lengkap && !in_array($current_script, ['profil_toko.php', 'proses_profil_toko.php', 'logout.php'])) {
    header("Location: " . BASE_URL . "/penjual/profil_toko.php");
    exit();
}

// Ambil Nama Website dari Database
$nama_website_default = 'SmeksabaShop';
$query_nama_web = "SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'nama_website' LIMIT 1";
$result_nama_web = mysqli_query($koneksi, $query_nama_web);
$nama_website = ($result_nama_web && mysqli_num_rows($result_nama_web) > 0) ? mysqli_fetch_assoc($result_nama_web)['nilai_pengaturan'] : $nama_website_default;

// Ambil Nama Toko Penjual
$query_toko = "SELECT nama_toko FROM toko WHERE user_id = $user_id LIMIT 1";
$result_toko = mysqli_query($koneksi, $query_toko);
$nama_toko = ($result_toko && mysqli_num_rows($result_toko) > 0) ? mysqli_fetch_assoc($result_toko)['nama_toko'] : $_SESSION['username'];

// Ambil jumlah pesan yang belum dibaca
$query_pesan = "SELECT COUNT(id) as total FROM pesan WHERE penerima_id = $user_id AND sudah_dibaca = 0";
$result_pesan = mysqli_query($koneksi, $query_pesan);
$jumlah_pesan_baru = ($result_pesan) ? mysqli_fetch_assoc($result_pesan)['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="<?php echo BASE_URL; ?>/favicon.png" type="image/x-icon">
    
    <link href="<?php echo BASE_URL; ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Panel Penjual' : 'Panel Penjual'; ?></title>
    <style>
        /* CSS untuk layout panel penjual */
        body { display: flex; background-color: #f8f9fa; }
        .top-bar { background-color: #198754; color: white; padding: 0.75rem 1rem; position: fixed; top: 0; left: 0; width: 100%; z-index: 1030; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,.1); }
        .menu-toggle { font-size: 1.5rem; background: none; border: none; color: white; margin-right: 1rem; }
        .sidebar { width: 250px; background-color: #212529; position: fixed; top: 0; left: 0; transform: translateX(-250px); transition: transform 0.3s ease-in-out; z-index: 1040; display: flex; flex-direction: column; height: 100%; }
        .sidebar-header { padding: 1rem 1.2rem; color: #fff; border-bottom: 1px solid #495057; flex-shrink: 0; }
        .sidebar-header h5 { margin: 0; font-size: 1.1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-header small { color: #adb5bd; }
        .sidebar-nav-container { flex-grow: 1; overflow-y: auto; padding-bottom: 1rem; }
        .sidebar-nav-container::-webkit-scrollbar { width: 8px; }
        .sidebar-nav-container::-webkit-scrollbar-thumb { background-color: #495057; border-radius: 4px; }
        .sidebar.show { transform: translateX(0); }
        .sidebar .nav-link { color: #c2c7d0; padding: .85rem 1.2rem; border-left: 3px solid transparent; display: flex; align-items: center; font-size: 0.95rem; }
        .sidebar .nav-link .bi { margin-right: 0.8rem; font-size: 1.1rem; width: 20px; text-align: center; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #343a40; border-left: 3px solid #198754; }
        .sidebar .sidebar-footer { flex-shrink: 0; border-top: 1px solid #495057; padding-top: .5rem; padding-bottom: .5rem; }
        .content-wrapper { flex-grow: 1; padding: 20px; padding-top: 80px; width: 100%; margin-left: 0; transition: margin-left 0.3s ease-in-out; }
        @media (min-width: 992px) { .sidebar { transform: translateX(0); } .content-wrapper { margin-left: 250px; } .menu-toggle { display: none; } }
        @media (max-width: 991.98px) { .sidebar.show + .overlay + .content-wrapper { margin-left: 0; } }
        .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1035; }
        .overlay.show { display: block; }

        /* CSS BARU UNTUK FITUR CHAT */
        .chat-list-item { display: flex; align-items: center; padding: 1rem; border-bottom: 1px solid #eee; text-decoration: none; color: #333; transition: background-color 0.2s; }
        .chat-list-item:hover { background-color: #f8f9fa; }
        .chat-list-avatar img { width: 50px; height: 50px; object-fit: cover; }
        .chat-list-info { flex-grow: 1; overflow: hidden; }
        .chat-list-info p { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0; font-size: 0.9em; color: #6c757d; }
        .chat-list-meta { text-align: right; font-size: 0.8em; color: #6c757d; }
        .unread-badge { background-color: #dc3545; color: white; font-size: 0.75em; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-top: 5px; }

        .chat-container { display: flex; flex-direction: column; height: calc(100vh - 200px); }
        .chat-box { flex-grow: 1; background-color: #f0f2f5; overflow-y: auto; display: flex; flex-direction: column-reverse; }
        .chat-content { display: flex; flex-direction: column; padding: 1rem; }
        .chat-bubble { padding: 10px 15px; border-radius: 20px; max-width: 75%; word-wrap: break-word; margin-bottom: 5px; }
        .bubble-sent { background-color: #d9fdd3; border-bottom-right-radius: 5px; align-self: flex-end; }
        .bubble-received { background-color: #ffffff; border-bottom-left-radius: 5px; align-self: flex-start; }
    </style>
</head>
<body>

<div class="top-bar no-print">
    <button class="menu-toggle" id="menuToggle">☰</button>
    <h5 class="mb-0"><?php echo htmlspecialchars($nama_website); ?> - Panel Penjual</h5>
</div>

<div class="sidebar no-print" id="sellerSidebar">
    <div class="sidebar-header">
        <h5 title="<?php echo htmlspecialchars($nama_toko); ?>"><?php echo htmlspecialchars($nama_toko); ?></h5>
        <small>@<?php echo htmlspecialchars($_SESSION['username']); ?></small>
    </div>
    
    <div class="sidebar-nav-container">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between" href="<?php echo BASE_URL; ?>/penjual/pesan.php">
                    <span><i class="bi bi-chat-dots-fill"></i> Pesan</span>
                    <?php if ($jumlah_pesan_baru > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $jumlah_pesan_baru; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item mt-3">
                <small class="text-muted px-3 text-uppercase">Manajemen Pesanan</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/pesanan_masuk.php"><i class="bi bi-box-seam"></i> Pesanan Masuk</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/riwayat_pesanan.php"><i class="bi bi-clock-history"></i> Riwayat Pesanan</a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-muted px-3 text-uppercase">Manajemen Toko</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/produk_saya.php"><i class="bi bi-grid-3x3-gap-fill"></i> Produk Saya</a>
            </li>
<li class="nav-item">
    <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/kelola_kurir.php"><i class="bi bi-person-badge"></i> Kelola Kurir Lokal</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/laporan_kurir.php"><i class="bi bi-clipboard-data"></i> Laporan Kurir</a>
</li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/kelola_voucher.php"><i class="bi bi-ticket-percent-fill"></i> Kelola Voucher</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/profil_toko.php"><i class="bi bi-shop-window"></i> Profil Toko</a>
            </li>

            <li class="nav-item mt-3">
                <small class="text-muted px-3 text-uppercase">Keuangan</small>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/saldo.php"><i class="bi bi-wallet2"></i> Saldo & Penarikan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo BASE_URL; ?>/penjual/laporan_keuangan.php"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Keuangan</a>
            </li>
        </ul>
    </div>

    <ul class="nav flex-column sidebar-footer">
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/"><i class="bi bi-arrow-left-square"></i> Kembali ke Situs</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </li>
    </ul>
</div>

<div class="overlay no-print" id="overlay"></div>

<div class="content-wrapper">