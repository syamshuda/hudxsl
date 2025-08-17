<?php
// /api_ikuti_toko.php
require_once 'config/database.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Terjadi kesalahan.', 'is_following' => false, 'follower_count' => 0];

try {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pembeli') {
        throw new Exception('Harap login sebagai pembeli untuk mengikuti toko.');
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['toko_id'])) {
        throw new Exception('Permintaan tidak valid.');
    }
    
    $user_id = $_SESSION['user_id'];
    $toko_id = (int)$_POST['toko_id'];

    // Cek apakah user sudah mengikuti toko ini
    $stmt_check = $koneksi->prepare("SELECT id FROM pengikut_toko WHERE user_id = ? AND toko_id = ?");
    $stmt_check->bind_param("ii", $user_id, $toko_id);
    $stmt_check->execute();
    $is_following = $stmt_check->get_result()->num_rows > 0;
    $stmt_check->close();
    
    mysqli_begin_transaction($koneksi);

    if ($is_following) {
        // Jika sudah mengikuti, maka batalkan (unfollow)
        $stmt_unfollow = $koneksi->prepare("DELETE FROM pengikut_toko WHERE user_id = ? AND toko_id = ?");
        $stmt_unfollow->bind_param("ii", $user_id, $toko_id);
        $stmt_unfollow->execute();
        
        $stmt_update_count = $koneksi->prepare("UPDATE toko SET jumlah_pengikut = GREATEST(0, jumlah_pengikut - 1) WHERE id = ?");
        $stmt_update_count->bind_param("i", $toko_id);
        $stmt_update_count->execute();
        
        $response['message'] = 'Batal mengikuti toko.';
        $response['is_following'] = false;
    } else {
        // Jika belum, maka ikuti (follow)
        $stmt_follow = $koneksi->prepare("INSERT INTO pengikut_toko (user_id, toko_id) VALUES (?, ?)");
        $stmt_follow->bind_param("ii", $user_id, $toko_id);
        $stmt_follow->execute();

        $stmt_update_count = $koneksi->prepare("UPDATE toko SET jumlah_pengikut = jumlah_pengikut + 1 WHERE id = ?");
        $stmt_update_count->bind_param("i", $toko_id);
        $stmt_update_count->execute();
        
        $response['message'] = 'Berhasil mengikuti toko!';
        $response['is_following'] = true;
    }
    
    mysqli_commit($koneksi);
    
    // Ambil jumlah pengikut terbaru untuk dikirim kembali ke browser
    $result_count = mysqli_query($koneksi, "SELECT jumlah_pengikut FROM toko WHERE id = $toko_id");
    $response['follower_count'] = mysqli_fetch_assoc($result_count)['jumlah_pengikut'] ?? 0;
    $response['success'] = true;

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$koneksi->close();
?>