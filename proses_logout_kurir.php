<?php
// /kurir/proses_logout_kurir.php (Versi Final)
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Arahkan kembali ke halaman login kurir
header("Location: login.php");
exit();
?>