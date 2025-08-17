<?php
// /penjual/proses_profil_toko.php (Versi Final dengan Upload Banner)
require_once '../config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
    exit();
}

// Fungsi bantuan untuk mengelola upload file gambar (logo dan banner)
function upload_gambar_toko($file_input, $gambar_lama, $target_sub_dir) {
    if (isset($file_input) && $file_input['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/" . $target_sub_dir . "/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        // Membuat nama file yang unik untuk menghindari penimpaan file
        $nama_gambar_baru = uniqid() . '-' . basename($file_input["name"]);
        $target_file = $target_dir . $nama_gambar_baru;
        
        if (move_uploaded_file($file_input["tmp_name"], $target_file)) {
            // Hapus gambar lama jika ada dan bukan gambar default
            if (!empty($gambar_lama) && strpos($gambar_lama, 'default_') === false && file_exists($target_dir . $gambar_lama)) {
                unlink($target_dir . $gambar_lama);
            }
            return $nama_gambar_baru;
        }
    }
    return $gambar_lama; // Jika gagal upload atau tidak ada file baru, kembalikan nama gambar yang lama
}

$user_id = $_SESSION['user_id'];
$toko_id = (int)$_POST['toko_id'];

// Verifikasi kepemilikan toko
$stmt_verify = $koneksi->prepare("SELECT id FROM toko WHERE id = ? AND user_id = ?");
$stmt_verify->bind_param("ii", $toko_id, $user_id);
$stmt_verify->execute();
if ($stmt_verify->get_result()->num_rows !== 1) {
    header("Location: profil_toko.php?status=gagal&pesan=Akses tidak sah.");
    exit();
}
$stmt_verify->close();


// Ambil semua data dari form
$nama_toko = trim($_POST['nama_toko']);
$deskripsi = trim($_POST['deskripsi']);
$alamat_lengkap = trim($_POST['alamat_lengkap']);
$provinsi = trim($_POST['provinsi']);
$kota = trim($_POST['kota']);
$kecamatan = trim($_POST['kecamatan']);
$kode_pos = trim($_POST['kode_pos']);

// Validasi data
if (empty($nama_toko) || empty($alamat_lengkap) || empty($provinsi) || empty($kota) || empty($kecamatan) || empty($kode_pos)) {
    header("Location: profil_toko.php?status=gagal&pesan=Semua kolom wajib diisi.");
    exit();
}

// Proses upload logo dan banner menggunakan fungsi bantuan
$nama_logo_baru = upload_gambar_toko($_FILES['logo_toko'], $_POST['logo_lama'], 'logo_toko');
$nama_banner_baru = upload_gambar_toko($_FILES['banner_toko'], $_POST['banner_lama'], 'banners');


// Update data toko di database
$stmt_update = $koneksi->prepare(
    "UPDATE toko SET 
        nama_toko = ?, 
        deskripsi = ?, 
        logo_toko = ?, 
        banner_toko = ?, 
        alamat_lengkap = ?, 
        provinsi = ?, 
        kota = ?, 
        kecamatan = ?, 
        kode_pos = ? 
    WHERE id = ?"
);
$stmt_update->bind_param("sssssssssi", 
    $nama_toko, $deskripsi, $nama_logo_baru, $nama_banner_baru, 
    $alamat_lengkap, $provinsi, $kota, $kecamatan, $kode_pos, 
    $toko_id
);

if ($stmt_update->execute()) {
    header("Location: profil_toko.php?status=sukses");
} else {
    header("Location: profil_toko.php?status=gagal&pesan=Gagal menyimpan data ke database.");
}

$stmt_update->close();
$koneksi->close();
exit();
?>