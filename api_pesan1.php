<?php
// /api_pesan.php (Versi Perbaikan Final)
require_once 'config/database.php';
header('Content-Type: application/json');

// Membersihkan output buffer untuk mencegah karakter tak terduga
ob_clean();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send_message') {
        $penerima_id = (int)($_POST['penerima_id'] ?? 0);
        $isi_pesan = trim($_POST['isi_pesan'] ?? '');
        $response = ['success' => false, 'message' => 'Gagal mengirim pesan.'];

        if (!empty($isi_pesan) && $penerima_id > 0) {
            $stmt = $koneksi->prepare("INSERT INTO pesan (pengirim_id, penerima_id, isi_pesan) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $penerima_id, $isi_pesan);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Pesan terkirim.';
            } else {
                 $response['message'] = 'Gagal menyimpan pesan ke database.';
            }
        } else {
            $response['message'] = 'Pesan atau penerima tidak valid.';
        }
        echo json_encode($response);
        exit(); // <-- Tambahan penting

    } elseif ($action === 'get_conversations') {
        // Logika untuk mengambil percakapan (dari solusi sebelumnya, sudah optimal)
        $query_percakapan = "
            SELECT
                u.id, u.username, u.nama_lengkap, t.nama_toko,
                sub.last_message, sub.last_message_time,
                (SELECT COUNT(*) FROM pesan WHERE pengirim_id = u.id AND penerima_id = ? AND sudah_dibaca = 0) as unread_count
            FROM (
                SELECT
                    IF(pengirim_id = ?, penerima_id, pengirim_id) as other_user_id,
                    MAX(created_at) as last_message_time,
                    SUBSTRING_INDEX(GROUP_CONCAT(isi_pesan ORDER BY created_at DESC), ',', 1) as last_message
                FROM pesan
                WHERE pengirim_id = ? OR penerima_id = ?
                GROUP BY IF(pengirim_id = ?, penerima_id, pengirim_id)
            ) AS sub
            JOIN users u ON u.id = sub.other_user_id
            LEFT JOIN toko t ON u.id = t.user_id
            ORDER BY sub.last_message_time DESC
        ";
        $stmt = $koneksi->prepare($query_percakapan);
        $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        $response['conversations'] = $conversations;
        $stmt->close();
        
    } elseif ($action === 'get_messages' && isset($_GET['chat_with'])) {
        // Logika untuk mengambil isi pesan
        $chat_with_id = (int)$_GET['chat_with'];
        $stmt_update = $koneksi->prepare("UPDATE pesan SET sudah_dibaca = 1 WHERE pengirim_id = ? AND penerima_id = ?");
        $stmt_update->bind_param("ii", $chat_with_id, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        $query_pesan = "SELECT p.*, u.nama_lengkap as pengirim_nama FROM pesan p JOIN users u ON p.pengirim_id = u.id WHERE (pengirim_id = ? AND penerima_id = ?) OR (pengirim_id = ? AND penerima_id = ?) ORDER BY created_at ASC";
        $stmt = $koneksi->prepare($query_pesan);
        $stmt->bind_param("iiii", $user_id, $chat_with_id, $chat_with_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        $response['messages'] = $messages;
        $stmt->close();

    } else {
        throw new Exception('Aksi tidak valid.');
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    if (isset($koneksi)) $koneksi->close();
    exit();
}