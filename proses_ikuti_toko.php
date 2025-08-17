<?php
// /proses_ikuti_toko.php
require_once 'config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

$pembeli_id = $_SESSION['user_id'];
$toko_id = (int)$_POST['toko_id'];
$action = $_POST['action'];

header('Content-Type: application/json');

try {
    if ($action === 'follow') {
        $stmt = $koneksi->prepare("INSERT IGNORE INTO pengikut_toko (pembeli_id, toko_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $pembeli_id, $toko_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
    } elseif ($action === 'unfollow') {
        $stmt = $koneksi->prepare("DELETE FROM pengikut_toko WHERE pembeli_id = ? AND toko_id = ?");
        $stmt->bind_param("ii", $pembeli_id, $toko_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$koneksi->close();
?>