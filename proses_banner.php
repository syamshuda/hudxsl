<?php
require_once '../config/database.php';

// Proteksi
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /auth/login.php");
        exit();
        }

        $action = $_POST['action'];

        if ($action == 'tambah') {
            $nama = trim($_POST['nama_banner']);
                $link = trim($_POST['link_tujuan']);
                    $urutan = (int)$_POST['urutan'];

                        // Proses upload gambar
                            $gambar = $_FILES['gambar_banner'];
                                if ($gambar['error'] === UPLOAD_ERR_OK) {
                                        $target_dir = "../uploads/banner/";
                                                if (!is_dir($target_dir)) {
                                                            mkdir($target_dir, 0755, true);
                                                                    }
                                                                            $nama_gambar = uniqid() . '-' . basename($gambar["name"]);
                                                                                    $target_file = $target_dir . $nama_gambar;
                                                                                            
                                                                                                    if (move_uploaded_file($gambar["tmp_name"], $target_file)) {
                                                                                                                $stmt = $koneksi->prepare("INSERT INTO promo_banner (nama_banner, gambar_banner, link_tujuan, urutan) VALUES (?, ?, ?, ?)");
                                                                                                                            $stmt->bind_param("sssi", $nama, $nama_gambar, $link, $urutan);
                                                                                                                                        $stmt->execute();
                                                                                                                                                }
                                                                                                                                                    }
                                                                                                                                                    } elseif ($action == 'hapus') {
                                                                                                                                                        $id = (int)$_POST['banner_id'];
                                                                                                                                                            $gambar_file = $_POST['gambar_file'];

                                                                                                                                                                $stmt = $koneksi->prepare("DELETE FROM promo_banner WHERE id = ?");
                                                                                                                                                                    $stmt->bind_param("i", $id);
                                                                                                                                                                        if ($stmt->execute()) {
                                                                                                                                                                                $file_path = "../uploads/banner/" . $gambar_file;
                                                                                                                                                                                        if (file_exists($file_path)) {
                                                                                                                                                                                                    unlink($file_path);
                                                                                                                                                                                                            }
                                                                                                                                                                                                                }
                                                                                                                                                                                                                }

                                                                                                                                                                                                                header("Location: kelola_banner.php");
                                                                                                                                                                                                                exit();
                                                                                                                                                                                                                ?>