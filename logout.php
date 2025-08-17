<?php
// /auth/logout.php

// Panggil file ini untuk memastikan session dimulai dengan benar
require_once '../config/database.php';

// Hapus semua variabel session
session_unset();

// Hancurkan session
session_destroy();

// Arahkan kembali ke halaman login dengan pesan
header("Location: login.php?logout=1");
exit();
?>