<?php
// /penjual/proses_lengkapi_alamat.php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'penjual' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        $user_id = $_SESSION['user_id'];
        $provinsi = trim($_POST['provinsi'] ?? '');
        $kota = trim($_POST['kota'] ?? '');
        $kecamatan = trim($_POST['kecamatan'] ?? '');
        $kelurahan = trim($_POST['kelurahan'] ?? '');
        $alamat_lengkap = trim($_POST['alamat_lengkap'] ?? '');

        try {
            if (empty($provinsi) || empty($kota) || empty($kecamatan) || empty($kelurahan) || empty($alamat_lengkap)) {
                    throw new Exception("Semua field alamat wajib diisi.");
                        }

                            $stmt_update = $koneksi->prepare("UPDATE toko SET provinsi = ?, kota = ?, kecamatan = ?, kelurahan = ?, alamat_lengkap = ? WHERE user_id = ?");
                                
                                    if ($stmt_update === false) {
                                            throw new Exception("Gagal menyiapkan query: " . $koneksi->error);
                                                }
                                                    
                                                        $stmt_update->bind_param("sssssi", $provinsi, $kota, $kecamatan, $kelurahan, $alamat_lengkap, $user_id);

                                                            if (!$stmt_update->execute()) {
                                                                    throw new Exception("Gagal menyimpan alamat toko.");
                                                                        }
                                                                            
                                                                                $stmt_update->close();
                                                                                    
                                                                                        header("Location: index.php?status=sukses_alamat");
                                                                                            exit();

                                                                                            } catch (Exception $e) {
                                                                                                header("Location: lengkapi_alamat_toko.php?status=gagal&pesan=" . urlencode($e->getMessage()));
                                                                                                    exit();
                                                                                                    }
                                                                                                    ?>