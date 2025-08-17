<?php
// /penjual/proses_profil_toko.php
require_once '../config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        $toko_id = (int)$_POST['toko_id'];
        $nama_toko = trim($_POST['nama_toko']);
        $deskripsi = trim($_POST['deskripsi']);
        $logo_lama = $_POST['logo_lama'];
        $user_id = $_SESSION['user_id'];

        // Data Alamat Baru
        $alamat_lengkap = trim($_POST['alamat_lengkap']);
        $provinsi = trim($_POST['provinsi']);
        $kota = trim($_POST['kota']);
        $kecamatan = trim($_POST['kecamatan']);
        $kode_pos = trim($_POST['kode_pos']);

        // Validasi sederhana untuk alamat
        if (empty($alamat_lengkap) || empty($provinsi) || empty($kota) || empty($kecamatan) || empty($kode_pos)) {
            header("Location: profil_toko.php?status=gagal&pesan=Alamat pengiriman wajib diisi lengkap.");
                exit();
                }

                // Verifikasi kepemilikan toko
                $stmt_verify = $koneksi->prepare("SELECT id FROM toko WHERE id = ? AND user_id = ?");
                $stmt_verify->bind_param("ii", $toko_id, $user_id);
                $stmt_verify->execute();
                if ($stmt_verify->get_result()->num_rows !== 1) {
                    header("Location: profil_toko.php?status=gagal");
                        exit();
                        }
                        $stmt_verify->close();

                        // Proses upload logo baru jika ada
                        $nama_logo_baru = $logo_lama;
                        $logo_toko = $_FILES['logo_toko'];
                        if (isset($logo_toko) && $logo_toko['error'] === UPLOAD_ERR_OK) {
                            $target_dir = "../uploads/logo_toko/";
                                if (!is_dir($target_dir)) {
                                        mkdir($target_dir, 0755, true);
                                            }
                                                $nama_logo_baru = uniqid() . '-' . basename($logo_toko["name"]);
                                                    $target_file = $target_dir . $nama_logo_baru;
                                                        
                                                            if (move_uploaded_file($logo_toko["tmp_name"], $target_file)) {
                                                                    // Hapus logo lama jika bukan default dan jika upload berhasil
                                                                            if ($logo_lama !== 'default_logo.png' && !empty($logo_lama) && file_exists($target_dir . $logo_lama)) {
                                                                                        unlink($target_dir . $logo_lama);
                                                                                                }
                                                                                                    } else {
                                                                                                            $nama_logo_baru = $logo_lama; // Jika gagal upload, pakai nama logo lama
                                                                                                                }
                                                                                                                }

                                                                                                                // Update data toko dengan kolom alamat baru
                                                                                                                $stmt_update = $koneksi->prepare("UPDATE toko SET nama_toko = ?, deskripsi = ?, logo_toko = ?, alamat_lengkap = ?, provinsi = ?, kota = ?, kecamatan = ?, kode_pos = ? WHERE id = ?");
                                                                                                                $stmt_update->bind_param("ssssssssi", $nama_toko, $deskripsi, $nama_logo_baru, $alamat_lengkap, $provinsi, $kota, $kecamatan, $kode_pos, $toko_id);

                                                                                                                if ($stmt_update->execute()) {
                                                                                                                    header("Location: profil_toko.php?status=sukses");
                                                                                                                    } else {
                                                                                                                        header("Location: profil_toko.php?status=gagal");
                                                                                                                        }

                                                                                                                        $stmt_update->close();
                                                                                                                        $koneksi->close();
                                                                                                                        ?>