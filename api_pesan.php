<?php
// /api_pesan.php (Versi Final dengan Query Stabil)
require_once 'config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$response = [];

try {
    if ($action === 'get_conversations') {
        // PERBAIKAN: Query ditulis ulang agar lebih sederhana dan stabil
        $query_percakapan = "
            SELECT 
                u.id, 
                u.username, 
                u.nama_lengkap, 
                t.nama_toko,
                p_last.isi_pesan AS last_message,
                p_last.created_at AS last_message_time,
                (SELECT COUNT(*) FROM pesan WHERE pengirim_id = u.id AND penerima_id = ? AND sudah_dibaca = 0) as unread_count
            FROM (
                SELECT GREATEST(pengirim_id, penerima_id) as user_max, LEAST(pengirim_id, penerima_id) as user_min, MAX(id) as max_id
                FROM pesan
                WHERE ? IN (pengirim_id, penerima_id)
                GROUP BY user_max, user_min
            ) AS conv_ids
            JOIN pesan p_last ON conv_ids.max_id = p_last.id
            JOIN users u ON u.id = IF(conv_ids.user_max = ?, conv_ids.user_min, conv_ids.user_max)
            LEFT JOIN toko t ON u.id = t.user_id
            ORDER BY p_last.created_at DESC
        ";

        $stmt = $koneksi->prepare($query_percakapan);
        $stmt->bind_param("iii", $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        $response['conversations'] = $conversations;
        $stmt->close();

    } elseif ($action === 'get_messages' && isset($_GET['chat_with'])) {
        $chat_with_id = (int)$_GET['chat_with'];
        $stmt_update = $koneksi->prepare("UPDATE pesan SET sudah_dibaca = 1 WHERE pengirim_id = ? AND penerima_id = ?");
        $stmt_update->bind_param("ii", $chat_with_id, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
        $query_pesan = "SELECT p.* FROM pesan p WHERE (pengirim_id = ? AND penerima_id = ?) OR (pengirim_id = ? AND penerima_id = ?) ORDER BY created_at ASC";
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
    
    } elseif ($action === 'get_user_info' && isset($_GET['user_id'])) {
        $get_user_id = (int)$_GET['user_id'];
        $stmt_user = $koneksi->prepare("SELECT u.id, u.nama_lengkap, t.nama_toko FROM users u LEFT JOIN toko t ON u.id = t.user_id WHERE u.id = ?");
        $stmt_user->bind_param("i", $get_user_id);
        $stmt_user->execute();
        $user_info = $stmt_user->get_result()->fetch_assoc();
        $response['user'] = $user_info;
        $stmt_user->close();
    } else {
        throw new Exception('Invalid action.');
    }

} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
$koneksi->close();